<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events;

use Ox\Core\CMbObject;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CExchangeHL7v3;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CHL7;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CReceiverHL7v2;

/**
 * Class CHL7Event
 * Event HL7
 */
class CHL7Event extends CHEvent
{
    /** @var string */
    public $profil;

    /** @var string */
    public $transaction;

    /** @var string */
    public $code;

    /** @var string */
    public $struct_code;

    /** @var CHL7v2Message */
    public $message;

    /** @var string */
    public $msg_hl7;

    /** @var string */
    public $altered_content_message_id;

    /** @var CReceiverHL7v2 */
    public $_receiver;

    /** @var CInteropSender */
    public $_sender;

    /** @var CExchangeDataFormat */
    public $_data_format;

    /** @var CExchangeHL7v2 */
    public $_exchange_hl7v2;

    /** @var CExchangeHL7v3 */
    public $_exchange_hl7v3;

    /** @var string */
    public $_is_i18n;

    /**
     * Construct
     *
     * @param string|null $i18n i18n
     */
    function __construct($i18n = null)
    {
        $this->_is_i18n = $i18n;
    }

    /**
     * Get event class
     *
     * @param self $event Event HL7
     *
     * @return string
     */
    static function getEventClass($event)
    {
        $classname = "CHL7Event" . $event->event_type . $event->code;
        if ($event->message->i18n_code) {
            $classname .= "_" . $event->message->i18n_code;
        }

        return $classname;
    }

    /**
     * Get event version
     *
     * @param string $version      Version
     * @param string $message_name Message name
     * @param string $message_name Message name
     *
     * @return CHL7v2Event
     * @throws CHL7v2Exception
     */
    public static function getEventVersion(
        string $version,
        string $message_name,
        CInteropNorm $message_class = null
    ): CHL7v2Event {
        $hl7_version = null;
        foreach (CHL7::$versions as $_version => $_sub_versions) {
            if (in_array($version, $_sub_versions)) {
                $hl7_version = $_version;
            }
        }

        if (!$hl7_version) {
            throw new CHL7v2Exception(CHL7v2Exception::VERSION_UNKNOWN, $version);
        }

        /** @var CHL7v2Event $event_class */
        $event_classname = "CHL7{$hl7_version}Event{$message_name}";

        $event_class = new $event_classname();
        if ($message_class) {
            $event_class->profil      = $message_class->type;
            $event_class->transaction = $message_class::getTransaction($event_class->code);
        }

        return $event_class;
    }

    /**
     * @inheritdoc
     */
    function build($object)
    {
    }

    /**
     * Build specifics HL7 message (i18n)
     *
     * @param CMbObject $object Object to use
     *
     * @return void
     */
    function buildI18nSegments($object)
    {
    }

    /**
     * @inheritdoc
     */
    function handle($msg)
    {
    }
}
