<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\handlers;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Hl7\CReceiverHL7v2;

/**
 * Class CHL7DelegatedHandler
 * HL7 Object Handler
 */
class CHL7DelegatedHandler implements IShortNameAutoloadable
{
    /**
     * Trigger before event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onBeforeStore(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * If object is handled ?
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public static function isHandled(CStoredObject $mbObject): bool
    {
        return in_array($mbObject->_class, static::$handled);
    }

    /**
     * Trigger after event store
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onAfterStore(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Trigger before event merge
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onBeforeMerge(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Trigger when merge failed
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onMergeFailure(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Trigger after event merge
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onAfterMerge(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Is message supported ?
     *
     * @param string         $message     Message
     * @param string         $code        Code
     * @param CReceiverHL7v2 $receiver    Receiver
     * @param string|null    $profil      Profil
     * @param string|null    $transaction Transaction
     *
     * @return bool
     */
    public function isMessageSupported(
        string $message,
        string $code,
        CReceiverHL7v2 $receiver,
        ?string $profil = null,
        ?string $transaction = null
    ): bool {
        if (!$receiver->isMessageSupported("CHL7Event{$message}{$code}", $profil, $transaction)) {
            return false;
        }

        return true;
    }

    /**
     * Trigger before event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onBeforeDelete(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Trigger after event delete
     *
     * @param CStoredObject $mbObject Object
     *
     * @return bool
     */
    public function onAfterDelete(CStoredObject $mbObject): bool
    {
        if (!$this->isHandled($mbObject)) {
            return false;
        }

        return true;
    }

    /**
     * Send message
     *
     * @param string        $message  Message
     * @param string        $code     Code
     * @param CStoredObject $mbObject Object
     *
     * @return null|bool|CHEvent|string
     *
     * @throws CMbException
     */
    public function sendEvent(
        string $message,
        string $code,
        CStoredObject $mbObject,
        ?string $profil = null,
        ?string $transaction = null
    ) {
        /** @var CReceiverHL7v2 $receiver */
        $receiver = $mbObject->_receiver;

        if (!$code) {
            throw new CMbException("CITI-code-none");
        }
        $class = "CHL7v2Event" . $message . $code;

        if (!class_exists($class)) {
            trigger_error("class-CHL7v2Event" . $message . $code . "-not-found", E_USER_ERROR);
        }

        $event              = new $class();
        $event->profil      = $profil;
        $event->transaction = $transaction;

        return $receiver->sendEvent($event, $mbObject);
    }
}
