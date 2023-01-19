<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Logger\LoggerLevels;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hprimsante\Events\CHPrimSanteEvent;

/**
 * Exchanges HprimSante
 */
class CExchangeHprimSante extends CExchangeTabular
{
    static $messages = [
        "ADM" => "CADM",
        "REG" => "CREG",
        "ORU" => "CORU",
    ];

    // DB Table key
    public $exchange_hprimsante_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'exchange_hprimsante';
        $spec->key      = 'exchange_hprimsante_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["sender_class"]            = "enum list|CSenderFTP|CSenderSOAP|CSenderFileSystem show|0";
        $props["sender_id"]               .= " back|expediteur_hprimsante";
        $props["message_content_id"]      .= " back|messages_hprimsante";
        $props["acquittement_content_id"] .= " back|acquittements_hprimsante";
        $props["receiver_id"]             = "ref class|CReceiverHprimSante autocomplete|nom back|echanges";
        $props["object_class"]            = "enum list|CSejour|CPatient|CMedecin show|0";
        $props["object_id"]               .= " back|exchanges_hprimsante";
        $props["group_id"]                .= " back|exchanges_hprimsante";

        $props["_message"]      = "hpr";
        $props["_acquittement"] = "hpr";

        return $props;
    }

    /**
     * Get hprim sante config for one actor
     *
     * @param string $actor_guid Actor GUID
     *
     * @return CHPrimSanteConfig|void
     */
    function getConfigs($actor_guid)
    {
        [$sender_class, $sender_id] = explode("-", $actor_guid);

        $sender_hprimsante_config               = new CHPrimSanteConfig();
        $sender_hprimsante_config->sender_class = $sender_class;
        $sender_hprimsante_config->sender_id    = $sender_id;
        $sender_hprimsante_config->loadMatchingObject();

        return $this->_configs_format = $sender_hprimsante_config;
    }

    /**
     * @see parent::handler
     */
    function handle()
    {
        $operator_hpr_sante = new COperatorHPrimSante();

        return $operator_hpr_sante->event($this);
    }

    /**
     * @see parent::understand
     */
    public function understand(string $data, CInteropActor $actor = null): bool
    {
        if (!$this->isWellFormed($data, $actor)) {
            return false;
        }

        try {
            $hpr_message = $this->parseMessage($data, false, $actor);
        } catch (CHL7v2Exception $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_DEBUG);

            return false;
        }

        $hpr_message_evt = "CHPrimSante$hpr_message->event_name" . $hpr_message->type;

        foreach ($this->getFamily() as $_message) {
            $message_class = new $_message();
            $evenements    = $message_class->getEvenements();

            if (in_array($hpr_message_evt, $evenements)) {
                $this->_events_message_by_family[$_message][] = new $hpr_message_evt();

                return true;
            }
        }

        return false;
    }

    /**
     * @see parent::isWellFormed
     */
    function isWellFormed($data, CInteropActor $actor = null)
    {
        try {
            return CHPrimSanteMessage::isWellFormed($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * parse message
     *
     * @param String        $string     message
     * @param bool          $parse_body parse the body
     * @param CInteropActor $actor      actor
     *
     * @return CHPrimSanteMessage
     */
    function parseMessage($string, $parse_body = true, $actor = null)
    {
        $hpr_message = new CHPrimSanteMessage();

        if (!$this->_id && $actor) {
            $this->sender_id    = $actor->_id;
            $this->sender_class = $actor->_class;
        }

        $hpr_message->parse($string, $parse_body);

        return $hpr_message;
    }

    /**
     * @see parent::getFamily
     */
    function getFamily()
    {
        return self::$messages;
    }

    /**
     * @see parent::getMessage
     */
    function getMessage()
    {
        if ($this->_message !== null) {
            $hpr_message = $this->parseMessage($this->_message);

            $this->_doc_errors_msg   = !$hpr_message->isOK(CHL7v2Error::E_ERROR);
            $this->_doc_warnings_msg = !$hpr_message->isOK(CHL7v2Error::E_WARNING);

            $this->_message_object = $hpr_message;

            return $hpr_message;
        }

        return null;
    }

    /**
     * @see parent::getACK
     */
    function getACK()
    {
        if ($this->_acquittement !== null) {
            $hpr_ack = new CHPrimSanteMessage();
            $hpr_ack->parse($this->_acquittement);

            $this->_doc_errors_ack   = !$hpr_ack->isOK(CHL7v2Error::E_ERROR);
            $this->_doc_warnings_ack = !$hpr_ack->isOK(CHL7v2Error::E_WARNING);

            return $hpr_ack;
        }

        return null;
    }

    /**
     * populate the exchange
     *
     * @param CExchangeDataFormat $data_format data format
     * @param CHPrimSanteEvent    $event       evenement
     *
     * @return void
     */
    function populateExchange(CExchangeDataFormat $data_format, CHPrimSanteEvent $event)
    {
        $this->group_id     = $data_format->group_id;
        $this->sender_id    = $data_format->sender_id;
        $this->sender_class = $data_format->sender_class;
        $this->version      = $event->message->version;
        $this->type         = $event->type_liaison;
        $this->sous_type    = $event->type;
        $this->_message     = $data_format->_message;
    }

    /**
     * Generate acknowledgment
     *
     * @param CHPrimSanteAcknowledgment $dom_acq Acknowledgment
     * @param CHPrimSanteError[]        $errors  hprim sante errors
     * @param CMbObject                 $object  Object
     *
     * @return CHPrimSanteAcknowledgment
     */
    function setAck(CHPrimSanteAcknowledgment $dom_acq, $errors, CMbObject $object = null)
    {
        $acq = $dom_acq->generateAcknowledgment($errors, $object);

        return $this->populateExchangeACK($acq, $object);
    }

    /**
     * populate the Acknowledgment exchange
     *
     * @param CHPrimSanteAcknowledgment $ack      Acknowledgment
     * @param CMbObject                 $mbObject object
     *
     * @return mixed
     */
    function populateExchangeACK(CHPrimSanteAcknowledgment $ack, CMbObject $mbObject = null)
    {
        $msgAck = $ack->event_err->msg_hpr;

        if ($mbObject && $mbObject->_id) {
            $this->setObjectIdClass($mbObject);
            $this->setIdPermanent($mbObject);
        }

        $this->statut_acquittement = $ack->ack_code ? $ack->ack_code : "ok";
        $this->acquittement_valide = $ack->event_err->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;

        $this->_acquittement = $msgAck;
        $this->send_datetime = CMbDT::dateTime();
        $this->store();

        return $msgAck;
    }
}
