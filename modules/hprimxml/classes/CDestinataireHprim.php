<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Contracts\Client\FileClientInterface;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceFileSystem;

/**
 * Class CDestinataireHprim
 * Destinataire H'XML
 */
class CDestinataireHprim extends CInteropReceiver
{
    /** @var string[] */
    public const ACTORS_MANAGED = [
        self::ACTOR_APPFINE,
        self::ACTOR_TAMM,
        self::ACTOR_MEDIBOARD
    ];

    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [
        CSourceFTP::TYPE,
        CSourceSFTP::TYPE,
        CSourceSOAP::TYPE,
        CSourceFileSystem::TYPE,
    ];

    // DB Table key
    public $dest_hprim_id;

    // DB Fields
    public $register;
    public $code_appli;
    public $code_acteur;
    public $code_syst;
    public $display_errors;

    // Form fields
    public $_tag_hprimxml;

    /**
     * Initialize object specification
     *
     * @return CMbObjectSpec the spec
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'destinataire_hprim';
        $spec->key      = 'dest_hprim_id';
        $spec->messages = [
            "patients" => [
                "evenementPatient",
            ],
            "pmsi"     => [
                (CAppUI::conf("hprimxml send_diagnostic") == "evt_serveuretatspatient") ?
                    "evenementServeurEtatsPatient" : "evenementPMSI",
                "evenementServeurActe",
                "evenementFraisDivers",
                "evenementServeurIntervention",
            ],
            "stock"    => [
                "evenementMvtStocks",
            ],
        ];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["group_id"]       .= " back|destinataires_hprim";
        $props["register"]       = "bool notNull default|1";
        $props["code_appli"]     = "str";
        $props["code_acteur"]    = "str";
        $props["code_syst"]      = "str";
        $props["display_errors"] = "bool notNull default|1";

        $props["_tag_hprimxml"] = "str";

        return $props;
    }

    /**
     * Update the form (derived) fields plain fields
     *
     * @return void
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->code_syst = $this->code_syst ? $this->code_syst : $this->nom;

        $this->_tag_hprimxml = CHPrimXML::getObjectTag($this->group_id);
    }

    /**
     * Send event patient
     *
     * @param CHPrimXMLEvenementsPatients $dom_evt    Event
     * @param CMbObject                   $mbObject   Object
     * @param bool                        $referent   Referent
     * @param bool                        $initiateur Initiateur
     * @param int                         $group_id   Group id
     *
     * @return bool|string
     * @throws CMbException
     *
     */
    function sendEvenementPatient(
        CHPrimXMLEvenementsPatients $dom_evt,
        CMbObject $mbObject,
        $referent = null,
        $initiateur = null,
        $group_id = null
    ) {
        // Si pas actif
        if (!$this->actif) {
            return false;
        }

        if ($this->role != CAppUI::conf("instance_role")) {
            return false;
        }

        if (!$msg = $dom_evt->generateTypeEvenement($mbObject, $referent, $initiateur, $group_id)) {
            return false;
        }

        $exchange = $dom_evt->_ref_echange_hprim;

        if (!$exchange->message_valide) {
            return false;
        }

        if (!$this->synchronous) {
            return false;
        }

        $source = CExchangeSource::get("$this->_guid-evenementPatient");
        if (!$source->_id || !$source->active) {
            return false;
        }

        $source->setData($msg, false, $exchange);
        $exchange->send_datetime = CMbDT::dateTime();

        try {
            $client = $source->getClient();
            if ($client instanceof FileClientInterface || $client instanceof SOAPClientInterface) {
                $client->send();
            } else {
                throw new CMbException('CExchangeSource-msg-client not supported', $this->nom);
            }
        } catch (Exception $e) {
            throw new CMbException("CExchangeSource-no-response %s", $this->nom);
        }

        $exchange->response_datetime = CMbDT::dateTime();

        $acq = $source->getACQ();
        if (!$acq) {
            $exchange->store();

            return false;
        }

        $dom_acq = new CHPrimXMLAcquittementsPatients();
        $dom_acq->loadXML($acq);
        $dom_acq->_ref_echange_hprim = $exchange;
        $doc_valid                   = $dom_acq->schemaValidate(null, false, $this->display_errors);

        $exchange->statut_acquittement = $dom_acq->getStatutAcquittementPatient();
        $exchange->acquittement_valide = $doc_valid ? 1 : 0;
        $exchange->_acquittement       = $acq;

        $exchange->store();
    }

    /**
     * Send event PMSI
     *
     * @param CHPrimXMLEvenementsServeurActivitePmsi $dom_evt  Event
     * @param CMbObject                              $mbObject Object
     *
     * @return bool|string
     * @throws CMbException
     *
     */
    function sendEvenementPMSI(CHPrimXMLEvenementsServeurActivitePmsi $dom_evt, CMbObject $mbObject)
    {
        // Si pas actif
        if (!$this->actif) {
            return false;
        }

        if ($this->role != CAppUI::conf("instance_role")) {
            return false;
        }

        if (!$msg = $dom_evt->generateTypeEvenement($mbObject)) {
            return false;
        }

        $source = CExchangeSource::get("$this->_guid-$dom_evt->sous_type");
        if (!$source->_id || !$source->active) {
            return false;
        }

        $exchange = $dom_evt->_ref_echange_hprim;

        $source->setData($msg, false, $exchange);
        $exchange->send_datetime = CMbDT::dateTime();
        try {
            $client = $source->getClient();
            if ($client instanceof FileClientInterface || $client instanceof SOAPClientInterface) {
                $client->send();
            } else {
                throw new CMbException('CExchangeSource-msg-client not supported', $this->nom);
            }
        } catch (Exception $e) {
            throw new CMbException("CExchangeSource-no-response %s", $this->nom);
        }

        $exchange->response_datetime = CMbDT::dateTime();

        $acq = $source->getACQ();
        if (!$acq) {
            $exchange->store();

            return false;
        }

        $dom_acq = CHPrimXMLAcquittementsServeurActivitePmsi::getEvtAcquittement($dom_evt);
        $dom_acq->loadXML($acq);
        $dom_acq->_ref_echange_hprim = $exchange;
        $doc_valid                   = $dom_acq->schemaValidate(null, false, $this->display_errors);

        $exchange->statut_acquittement = $dom_acq->getStatutAcquittementServeurActivitePmsi();
        $exchange->acquittement_valide = $doc_valid ? 1 : 0;
        $exchange->_acquittement       = $acq;

        $exchange->store();

        if (CModule::getActive("appFineClient")) {
            CAppFineClient::generateIdexOperationId($this, $mbObject, $dom_acq);
        }
    }

    /**
     * Get object handlers
     *
     * @param CEAIObjectHandler $objectHandler Object handler
     *
     * @return mixed
     * @throws Exception
     */
    function getFormatObjectHandler(CEAIObjectHandler $objectHandler)
    {
        $hprim_object_handlers = CHPrimXML::getObjectHandlers();
        $object_handler_class  = CClassMap::getSN($objectHandler);
        if (array_key_exists($object_handler_class, $hprim_object_handlers)) {
            return $hprim_object_handlers[$object_handler_class];
        }

        return null;
    }
}
