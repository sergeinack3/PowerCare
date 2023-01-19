<?php

/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Interop\Hl7\Events\CHL7Event;
use Ox\Interop\Hl7\Events\CHL7v2EventACK;

/**
 * Class CHL7v2Acknowledgment
 * Acknowledgment v2 HL7
 */
class CHL7v2Acknowledgment implements CHL7Acknowledgment, IShortNameAutoloadable
{
    /** @var CHL7Event|null */
    public $event;
    /** @var CHL7v2EventACK */
    public $event_ack;
    /** @var CHL7v2Message */
    public $message;
    /** @var CHL7v2MessageXML */
    public $dom_message;
    /** @var string */
    public $message_control_id;
    /** @var string */
    public $ack_code;
    /** @var array|string */
    public $mb_error_codes;
    /** @var string */
    public $hl7_error_code;
    /** @var string */
    public $severity;
    /** @var string */
    public $comments;
    /** @var CMbObject */
    public $object;
    /** @var string */
    public $_mb_error_code;
    /** @var CExchangeHL7v2 */
    public $_ref_exchange_hl7v2;

    /**
     * @inheritdoc
     */
    function __construct(CHL7Event $event = null)
    {
        $this->event = $event;
    }

    /**
     * @inheritdoc
     */
    function handle($ack_hl7)
    {
        $this->message = new CHL7v2Message();
        $this->message->parse($ack_hl7);
        $this->dom_message = $this->message->toXML();

        return $this->dom_message;
    }

    /**
     * @inheritdoc
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

        $this->event_ack = new CHL7v2EventACK();
        $this->event_ack->build($this);
        $this->event_ack->flatten();

        $this->event_ack->msg_hl7 = utf8_encode($this->event_ack->msg_hl7);

        return $this->event_ack->msg_hl7;
    }

    /**
     * @inheritdoc
     */
    function getStatutAcknowledgment()
    {
        $xpath = new CHL7v2MessageXPath($this->dom_message);

        return $xpath->queryTextNode("//MSA/MSA.1");
    }
}
