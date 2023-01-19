<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprim21;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hprim21\Events\CHPREvent;

/**
 * Echanges Hprim21
 */
class CEchangeHprim21 extends CExchangeTabular
{
    static $messages = [
        "ADM" => "CADM",
        "REG" => "CREG",
        "ORU" => "CORU",
    ];

    // DB Table key
    public $echange_hprim21_id;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'echange_hprim21';
        $spec->key      = 'echange_hprim21_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["receiver_id"]             = "ref class|CDestinataireHprim21 autocomplete|nom back|echanges";
        $props["object_class"]            = "enum list|CSejour|CPatient|CMedecin show|0";
        $props["object_id"]               .= " back|exchanges_hprim21";
        $props["sender_id"]               .= " back|expediteur_hprim21";
        $props["message_content_id"]      .= " back|messages_hprim21";
        $props["acquittement_content_id"] .= " back|acquittements_hprim21";
        $props["group_id"]                .= " back|exchanges_hprim21";

        $props["_message"]      = "hpr";
        $props["_acquittement"] = "hpr";

        return $props;
    }

    function handle()
    {
        $operator_hpr = new COperatorHPR();

        return $operator_hpr->event($this);
    }

    public function understand(string $data, CInteropActor $actor = null): bool
    {
        if (!$this->isWellFormed($data, $actor)) {
            return false;
        }

        try {
            $hpr_message = $this->parseMessage($data, false, $actor);
        } catch (CHL7v2Exception $e) {
            return false;
        }

        $hpr_message_evt = "CHPrim21$hpr_message->event_name" . $hpr_message->type;

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

    function isWellFormed($data, CInteropActor $actor = null)
    {
        try {
            return CHPrim21Message::isWellFormed($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return CHPrim21Message
     */
    function parseMessage($string, $parse_body = true, $actor = null)
    {
        $hpr_message = new CHPrim21Message();

        if (!$this->_id && $actor) {
            $this->sender_id    = $actor->_id;
            $this->sender_class = $actor->_class;
        }

        $hpr_message->parse($string, $parse_body);

        return $hpr_message;
    }

    function getFamily()
    {
        return self::$messages;
    }

    /**
     * @return CHPrim21Message
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
     * @return CHPrim21Message
     */
    function getACK()
    {
        if ($this->_acquittement !== null) {
            $hpr_ack = new CHPrim21Message();
            $hpr_ack->parse($this->_acquittement);

            $this->_doc_errors_ack   = !$hpr_ack->isOK(CHL7v2Error::E_ERROR);
            $this->_doc_warnings_ack = !$hpr_ack->isOK(CHL7v2Error::E_WARNING);

            return $hpr_ack;
        }

        return null;
    }

    function populateExchange(CExchangeDataFormat $data_format, CHPREvent $event)
    {
        $this->group_id     = $data_format->group_id;
        $this->sender_id    = $data_format->sender_id;
        $this->sender_class = $data_format->sender_class;
        $this->version      = $event->message->version;
        $this->type         = $event->type_liaison;
        $this->sous_type    = $event->type;
        $this->_message     = $data_format->_message;
    }

    function populateErrorExchange(CHPrim21Acknowledgment $ack = null, CHL7Event $event = null)
    {
        /*if ($ack) {
          $msgAck = $ack->event_ack->msg_hl7;
          $this->_acquittement       = $ack->event_ack->msg_hl7;
          $this->statut_acquittement = $ack->ack_code;
          $this->acquittement_valide = $ack->event_ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
        }
        else {
          $this->message_valide      = $event->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
          $this->date_production     = CMbDT::dateTime();
          $this->send_datetime       = CMbDT::dateTime();
        }*/

        $this->store();
    }

    function setAckI(CHPrim21Acknowledgment $ack, $errors, CMbObject $object = null)
    {
        $ack->generateAcknowledgment("I", $errors, $object);

        return $this->populateExchangeACK($ack, $object);
    }

    function populateExchangeACK(CHPrim21Acknowledgment $ack, $object)
    {
        $msgAck = $ack->event_err->msg_hpr;

        $this->statut_acquittement = $ack->ack_code;
        $this->acquittement_valide = $ack->event_err->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;

        $this->_acquittement = $msgAck;
        $this->send_datetime = CMbDT::dateTime();
        $this->store();

        return $msgAck;
    }

    function setAckP(CHPrim21Acknowledgment $ack, $errors, CMbObject $object = null)
    {
        $ack->generateAcknowledgment("P", $errors, $object);

        return $this->populateExchangeACK($ack, $object);
    }

    function setAckT(CHPrim21Acknowledgment $ack, $mb_error_codes, $comments = null, CMbObject $mbObject = null)
    {
        //$ack->generateAcknowledgment("T", $mb_error_codes, "0", "T", $comments, $object);

        return $this->populateExchangeACK($ack, $object);
    }
}
