<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\V2\Handle;

use DOMDocument;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbString;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7Acknowledgment;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hl7\Events\CHL7v2EventACK;
use Ox\Interop\Hl7\Events\SWF\CHL7v2EventORR;

/**
 * Class ReceiveOrderMessageResponse
 * Receive order message response, message XML HL7
 */
class ReceiveOrderMessageResponse implements CHL7Acknowledgment, IShortNameAutoloadable
{
    /**
     * @var CHL7Event|null
     */
    public $event;

    /** @var CHL7v2EventACK */


    /** @var CHL7v2Message */
    public $message;

    /** @var CHL7v2MessageXML */
    public $dom_message;

    public $message_control_id;
    public $ack_code;
    public $mb_error_codes;
    public $hl7_error_code;
    public $severity;
    public $comments;


    /** @var CExchangeHL7v2 */
    public $_ref_exchange_hl7v2;

    /**
     * Construct
     *
     * @param CHL7Event $event Event HL7
     *
     * @return ReceiveOrderMessageResponse
     */
    function __construct(CHL7Event $event = null)
    {
        $this->event = $event;
    }

    /**
     * Handle acknowledgment
     *
     * @param string $ack_hl7 HL7 acknowledgment
     *
     * @return DOMDocument
     */
    function handle($ack_hl7)
    {
        $this->message = new CHL7v2Message();
        $this->message->parse($ack_hl7);
        $this->dom_message = $this->message->toXML();

        return $this->dom_message;
    }

    /**
     * Generate acknowledgment
     *
     * @param string $ack_code       Acknowledgment code
     * @param string $mb_error_codes Mediboard error code
     * @param null   $hl7_error_code HL7 error code
     * @param string $severity       Severity
     * @param null   $comments       Comments
     * @param null   $object         Object
     *
     * @return null|string
     */
    function generateAcknowledgment(
        $ack_code,
        $mb_error_codes,
        $hl7_error_code = null,
        $severity = "E",
        $comments = null,
        $object = null
    ) {
        $this->ack_code       = $ack_code;
        $this->mb_error_codes = $mb_error_codes;
        $this->hl7_error_code = $hl7_error_code;
        $this->severity       = $severity;
        $this->comments       = CMbString::removeAllHTMLEntities($comments);
        $this->object         = $object;

        $this->event->_exchange_hl7v2 = $this->_ref_exchange_hl7v2;

        $this->event_ack = new CHL7v2EventORR($this->event);
        $this->event_ack->build($this);
        $this->event_ack->flatten();

        $this->event_ack->msg_hl7 = utf8_encode($this->event_ack->msg_hl7);

        return $this->event_ack->msg_hl7;
    }

    /**
     * Get statut acknowledgment
     *
     * @return null
     */
    function getStatutAcknowledgment()
    {
    }
}
