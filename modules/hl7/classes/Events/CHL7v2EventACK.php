<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use Ox\Core\CMbArray;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentMSH;

/**
 * Classe CHL7v2EventACK
 * Represents a ACK message structure (see chapter 2.14.1)
 */
class CHL7v2EventACK extends CHL7v2Event implements CHL7EventACK
{
    /**
     * Construct
     *
     * @param string|null $i18n i18n
     *
     * @return CHL7v2EventACK
     */
    function __construct($i18n = null)
    {
        $this->event_type = "ACK";
    }

    /**
     * Build event
     *
     * @param CHL7Acknowledgment $object Object
     *
     * @return void
     * @see parent::build()
     *
     */
    function build($object)
    {
        $trigger = $object->event;

        $this->version   = $trigger->message->extension ?: $trigger->message->version;
        $this->msg_codes = [
            [
                $this->event_type,
                $trigger->code,
                $this->event_type,
            ],
        ];

        $this->_exchange_hl7v2 = $trigger->_exchange_hl7v2;
        $this->_receiver       = $trigger->_exchange_hl7v2->_ref_receiver;
        $this->_sender         = $trigger->_exchange_hl7v2->_ref_sender;

        // Création du message HL7
        $this->message       = new CHL7v2Message($this->version);
        $this->message->name = $this->msg_codes;

        // Message Header
        $this->addMSH();

        // Software Segment
        $this->addSFT();

        // Message Acknowledgment
        $this->addMSA($object);

        // Error
        if (is_array($object->mb_error_codes)) {
            if ($this->version < "2.5") {
                $first_mb_error_code = reset($object->mb_error_codes);
                if (is_array($first_mb_error_code)) {
                    $first_mb_error_code = CMbArray::get($first_mb_error_code, 'code');
                }
                $object->_mb_error_code = $first_mb_error_code;
                $this->addERR($object);
            } else {
                $global_severity = $object->severity;
                foreach ($object->mb_error_codes as $_err_data) {
                    $_mb_error_code = $_err_data;
                    $comments       = null;
                    $severity       = null;
                    if (is_array($_err_data)) {
                        $_mb_error_code = $_err_data['code'];
                        $comments       = $_err_data['comments'] ?? null;
                        $severity       = $_err_data['type'] ?? null;
                    }

                    $object->severity       = $severity ?: $global_severity;
                    $object->comments       = $comments;
                    $object->_mb_error_code = $_mb_error_code;
                    $this->addERR($object);
                }
            }
        } else {
            if ($object->mb_error_codes) {
                $object->_mb_error_code = $object->mb_error_codes;
                $this->addERR($object);
            }
        }

        $trigger_event = $object->event;
        // Validation error
        if ($errors = $trigger_event->message->errors) {
            foreach ($errors as $_error) {
                $this->addERR($object, $_error);
            }
        }

        // AppFine
        if (isset($object->object->_id_appFine)) {
            $this->addERR($object, null, true);
        }
    }

    /**
     * MSH - Represents an HL7 MSH message segment (Message Header)
     *
     * @return void
     */
    function addMSH()
    {
        /** @var CHL7v2SegmentMSH $MSH */
        $MSH = CHL7v2Segment::create("MSH", $this->message);
        $MSH->build($this);
    }

    /**
     * SFT - Represents an HL7 SFT message segment (Software Segment)
     *
     * @return void
     */
    function addSFT()
    {
    }

    /**
     * MSA - Represents an HL7 MSA message segment (Message Acknowledgment)
     *
     * @param CHL7Acknowledgment $acknowledgment Acknowledgment
     *
     * @return void
     */
    function addMSA(CHL7Acknowledgment $acknowledgment)
    {
        $MSA                 = CHL7v2Segment::create("MSA", $this->message);
        $MSA->acknowledgment = $acknowledgment;
        $MSA->build($this);
    }

    /**
     * ERR - Represents an HL7 ERR message segment (Error)
     *
     * @param CHL7Acknowledgment $acknowledgment    Acknowledgment
     * @param CHL7v2Error|null   $error             Error HL7
     * @param Bool               $add_ic_to_message add ic au message HL7
     *
     * @return void
     */
    function addERR(CHL7Acknowledgment $acknowledgment, $error = null, $add_ic_to_message = false)
    {
        $ERR                 = CHL7v2Segment::create("ERR", $this->message);
        $ERR->acknowledgment = $acknowledgment;
        $ERR->error          = $error;

        $ack_severity_mode = "IWE";
        $sender            = $this->_sender;
        if ($sender) {
            $sender->loadConfigValues();
            $ack_severity_mode = CMbArray::get($sender->_configs, "ack_severity_mode");
        }

        if ($error) {
            if ($ack_severity_mode != "IWE") {
                if (($ack_severity_mode == "I" || $ack_severity_mode == "E") && $error->level == CHL7v2Error::E_WARNING) {
                    return;
                }
            }
        }

        if ($ack_severity_mode != "IWE" && $acknowledgment->severity != "E" && $ack_severity_mode != $acknowledgment->severity) {
            return;
        }

        // appFine
        if ($add_ic_to_message) {
            $ERR->acknowledgment->add_ic_to_message = true;
        }

        $ERR->build($this);
    }

    /**
     * Get the message as a string
     *
     * @return string
     */
    function flatten()
    {
        $this->msg_hl7 = $this->message->flatten();
        $this->message->validate();
    }
}
