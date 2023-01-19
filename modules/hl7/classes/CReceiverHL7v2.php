<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\Contracts\Client\FileClientInterface;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAIObjectHandler;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Ihe\CIHE;
use Ox\Interop\Webservices\CSourceSOAP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceFileSystem;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Class CReceiverHL7v2
 * Receiver HL7v2
 */
class CReceiverHL7v2 extends CInteropReceiver
{
    /** @var string[] */
    public const ACTORS_MANAGED = [
        self::ACTOR_DOCTOLIB,
        self::ACTOR_APPFINE,
        self::ACTOR_GALAXIE,
        self::ACTOR_MEDIBOARD,
        self::ACTOR_TAMM,
    ];

    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [
        CSourceMLLP::TYPE,
        CSourceFTP::TYPE,
        CSourceSFTP::TYPE,
        CSourceSOAP::TYPE,
        CSourceHTTP::TYPE,
        CSourceFileSystem::TYPE,
    ];

    // DB Table key
    /** @var null */
    public $receiver_hl7v2_id;

    /** @var null */
    public $_extension;

    /** @var null */
    public $_i18n_code;

    /** @var null */
    public $_tag_hl7;

    /**
     * Get all receivers
     *
     * @param array $events_name
     * @param null  $group_id
     *
     * @return CStoredObject[]|CInteropReceiver[]
     * @throws Exception
     */
    public static function getReceivers($events_name = [], $group_id = null)
    {
        $receiver        = new self();
        $receiver->role  = CAppUI::conf("instance_role");
        $receiver->actif = "1";
        $receivers       = $receiver->loadMatchingList();

        $group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;

        /** @var CReceiverHL7v2 $_receiver */
        foreach ($receivers as $_receiver) {
            if ($_receiver->group_id != $group_id) {
                unset($receivers[$_receiver->_guid]);
                continue;
            }

            $objects = CInteropReceiver::getObjectsBySupportedEvents($events_name, $_receiver, true);
            foreach ($events_name as $_event_name) {
                if (!array_key_exists($_event_name, $objects)) {
                    unset($receivers[$_receiver->_guid]);
                    continue;
                }

                if (!$objects[$_event_name]) {
                    unset($receivers[$_receiver->_guid]);
                    continue;
                }
            }
        }

        return $receivers;
    }

    /**
     * @inheritDoc
     */
    public function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'receiver_hl7v2';
        $spec->key      = 'receiver_hl7v2_id';
        $spec->messages = [
            // HL7
            "ADT"     => ["CHL7ADT"],
            "MDM"     => ["CHL7MDM"],
            "MFN"     => ["CHL7MFN"],
            "ORU"     => ["CHL7ORU"],

            // IHE
            "PAM"     => ["evenementsPatient"],
            "PAM_FRA" => ["evenementsPatient"],
            "DEC"     => ["CDEC"],
            "SWF"     => ["CSWF"],
            "PDQ"     => ["CPDQ"],
            "PIX"     => ["CPIX"],
            "SINR"    => ["CSINR"],
            "LTW"     => ["CLTW"],
            "DRPT"    => ["CDRPT"],
        ];

        return $spec;
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $props             = parent::getProps();
        $props["group_id"] .= " back|receivers_hl7v2";

        $props["_tag_hl7"] = "str";

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_tag_hl7 = CHL7::getObjectTag($this->group_id);

        if (!$this->_configs) {
            $this->loadConfigValues();
        }
    }

    /**
     * @inheritDoc
     */
    public function check()
    {
        $this->completeField('group_id', 'actif', 'type');

        if ($this->type) {
            (new CInteropActorFactory())->receiver()->makeHL7v2($this->type)->checkDuplicate($this);
        }
    }

    /**
     * Checks whether the doctolib recipient is unique
     *
     * @param CReceiverHL7v2 $receiver_Hl7v2 Receiver HL7v2 create/modify
     *
     * @return bool
     * @throws Exception
     */
    public function checkDuplicate(CReceiverHL7v2 $receiver_Hl7v2): bool
    {
        return true;
    }

    /**
     * Get object handler
     *
     * @param CEAIObjectHandler $objectHandler Object handler
     *
     * @return mixed
     * @throws Exception
     */
    public function getFormatObjectHandler(CEAIObjectHandler $objectHandler)
    {
        $handlers     = CIHE::getObjectHandlers();
        $hl7_handlers = CHL7::getObjectHandlers();

        foreach ($hl7_handlers as $_handler_key => $_handler_class) {
            if (CMbArray::get($handlers, $_handler_key)) {
                if (is_array($handlers[$_handler_key])) {
                    $handlers[$_handler_key] = array_merge(
                        $handlers[$_handler_key],
                        is_array($_handler_class) ? $_handler_class : [$_handler_class]
                    );
                } else {
                    $handlers[$_handler_key] = array_merge(
                        [$handlers[$_handler_key]],
                        is_array($_handler_class) ? $_handler_class : [$_handler_class]
                    );
                }
            } else {
                $handlers[$_handler_key] = $_handler_class;
            }
        }

        $object_handler_class = CClassMap::getSN($objectHandler);
        if (array_key_exists($object_handler_class, $handlers)) {
            return $handlers[$object_handler_class];
        }
    }

    /**
     * Get HL7 version for one transaction
     *
     * @param string $transaction Transaction name
     *
     * @return null|string
     */
    public function getHL7Version(string $transaction): ?string
    {
        if (!$iti_hl7_version = CMbArray::get($this->_configs, $transaction . "_HL7_version")) {
            $iti_hl7_version = $this->_configs['HL7_version'];
        }

        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($iti_hl7_version, $_sub_versions)) {
                return $_version;
            }
        }

        return null;
    }

    /**
     * Get internationalization code
     *
     * @param string $transaction Transaction name
     *
     * @return null
     */
    public function getInternationalizationCode($transaction)
    {
        $iti_hl7_version = $this->_configs[$transaction . "_HL7_version"];
        if (preg_match("/([A-Z]{3})_(.*)/", $iti_hl7_version, $matches)) {
            $this->_i18n_code = $matches[1];
        }

        return $this->_i18n_code;
    }

    /**
     * @inheritdoc
     *
     * @param CHL7v2Event $evenement
     */
    public function sendEvent($evenement, $object, $data = [], $headers = [], $message_return = false, $soapVar = false)
    {
        if (!parent::sendEvent($evenement, $object, $data, $headers, $message_return, $soapVar)) {
            return null;
        }

        $evenement->_receiver = $this;

        // build_mode = Mode simplifié lors de la génération du message
        $this->loadConfigValues();
        CHL7v2Message::setBuildMode($this->_configs["build_mode"]);
        $evenement->build($object);
        CHL7v2Message::resetBuildMode();
        if (!$msg = $evenement->flatten()) {
            return null;
        }

        $exchange = $evenement->_exchange_hl7v2;

        // Application des règles de transformation
        $msg = $evenement->applySequences($msg, $this);
        if (isset($evenement->altered_content_message_id) && $evenement->altered_content_message_id) {
            $exchange->altered_content_id = $evenement->altered_content_message_id;
            $exchange->message_valide     = 1;
        }

        // Si l'échange est invalide
        if (!$exchange->message_valide) {
            return null;
        }

        // Si on n'est pas en synchrone
        if (!$this->synchronous) {
            return null;
        }

        // Si on n'a pas d'IPP et NDA
        if ($exchange->master_idex_missing) {
            return null;
        }

        $evt    = $this->getEventMessage($evenement->profil);
        $source = CExchangeSource::get("$this->_guid-$evt");

        if (!$source->_id || !$source->active) {
            return null;
        }

        if ($this->_configs["encoding"] == "UTF-8") {
            $msg = utf8_encode($msg);
        }

        $exchange->send_datetime = CMbDT::dateTime();

        $source->setData($msg, null, $exchange);
        try {
            $client = $source->getClient();
            if ($client instanceof FileClientInterface || $client instanceof SOAPClientInterface || $client instanceof MLLPClientInterface) {
                $client->send();
            } else {
                throw new CMbException('CExchangeSource-msg-client not supported', $this->nom);
            }
        } catch (Exception $e) {
            throw new CMbException("CExchangeSource-no-response %s", $this->nom);
        }

        $exchange->response_datetime = CMbDT::dateTime();

        $ack_data = $source->getACQ();

        if (!$ack_data) {
            $exchange->store();

            return null;
        }

        $data_format = CIHE::getEvent($exchange);

        $ack = new CHL7v2Acknowledgment($data_format);
        $ack->handle($ack_data);
        $exchange->statut_acquittement = $ack->getStatutAcknowledgment();
        $exchange->acquittement_valide = $ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
        $exchange->_acquittement       = $ack_data;
        $exchange->store();

        if (CModule::getActive("appFineClient") && $this->_configs["send_evenement_to_mbdmp"]) {
            CAppFineClient::generateIdexEventId($this, $object, $ack_data);
        }

        return $ack_data;
    }

    /**
     * Get event message
     *
     * @param string $profil Profil name
     *
     * @return mixed
     */
    public function getEventMessage($profil)
    {
        if (!array_key_exists($profil, $this->_spec->messages)) {
            return null;
        }

        return reset($this->_spec->messages[$profil]);
    }

    /**
     * @inheritDoc
     */
    public function isINSCompatible(): bool
    {
        if ($this->_configs) {
            $this->loadConfigValues();
        }

        if ($ITI_30_version = CMbArray::get($this->_configs, 'ITI30_HL7_version')) {
            if (preg_match("/([A-Z]{3})_(.*)/", $ITI_30_version, $matches)) {
                if (CMbArray::get($matches, 1) === 'FRA') {
                    $this->_is_ins_compatible = true;
                }
            }
        }

        return $this->_is_ins_compatible;
    }
}
