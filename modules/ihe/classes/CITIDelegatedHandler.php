<?php
/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Ihe;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CReceiverHL7v2;

/**
 * Class CITIDelegatedHandler
 * ITI Object Handler
 */
class CITIDelegatedHandler implements IShortNameAutoloadable
{
    static $handled = [];

    /**
     * If object is handled ?
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    static function isHandled(CStoredObject $mbObject)
    {
        return in_array($mbObject->_class, self::$handled);
    }

    /**
     * Get i18n code
     *
     * @param CInteropReceiver $receiver Receiver HL7v2
     *
     * @return string
     */
    function getI18nCode($receiver)
    {
        if (!isset($receiver->_i18n_code)) {
            return null;
        }

        return "_$receiver->_i18n_code";
    }

    /**
     * Is the message supported by the receiver ?
     *
     * @param string           $message  Message
     * @param string           $code     Code
     * @param CInteropReceiver $receiver Receiver HL7v2
     *
     * @return bool
     * @throws Exception
     */
    function isMessageSupported($message, $code, $receiver)
    {
        $i18n_code = $this->getI18nCode($receiver);
        if (!$receiver->isMessageSupported("CHL7Event{$message}{$code}{$i18n_code}")) {
            return false;
        }

        return true;
    }

    /**
     * Send message
     *
     * @param string        $profil      Profil
     * @param string        $transaction Transaction
     * @param string        $message     Message
     * @param string        $code        Code
     * @param CStoredObject $object      Object
     *
     * @return bool|CHEvent
     * @throws CMbException
     */
    function sendITI($profil, $transaction, $message, $code, CStoredObject $object)
    {
        /** @var CReceiverHL7v2 $receiver */
        $receiver = $object->_receiver;

        $build_empty_fields = $receiver->_configs['build_empty_fields'];
        if ($build_empty_fields !== 'no') {
            $emptied_fields = $object->getEmptyValuedFields();
            if ($build_empty_fields === 'restricted') {
                $fields_allowed_to_empty = explode(',', $receiver->_configs['fields_allowed_to_empty']);

                $emptied_fields = array_intersect($emptied_fields, $fields_allowed_to_empty);
            }

            foreach ($emptied_fields as $_emptied_field) {
                $object->$_emptied_field = "\"\"";
            }
        }

        if (!$code) {
            throw new CMbException("CITI-code-none");
        }

        $i18n_code = $this->getI18nCode($receiver);

        if ($i18n_code) {
            $profil = $profil . $i18n_code;
        }

        $hl7_version = $receiver->getHL7Version($transaction);
        $class       = "CHL7" . $hl7_version . "Event" . $message . $code . $i18n_code;

        if (!class_exists($class)) {
            trigger_error(
                "class-CHL7" . $hl7_version . "Event" . $message . $code . $i18n_code . "-not-found",
                E_USER_ERROR
            );
        }

        $event              = new $class();
        $event->profil      = $profil;
        $event->transaction = $transaction;

        return $receiver->sendEvent($event, $object);
    }
}
