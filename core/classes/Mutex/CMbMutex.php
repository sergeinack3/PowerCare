<?php
/**
 * @package Mediboard\Core\Mutex
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Mutex;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Sessions\CRedisSessionHandler;
use Ox\Core\Sessions\CSessionHandler;

/**
 * Manage locking files to deal with concurrency
 */
class CMbMutex implements IMbMutex
{
    static $drivers = [
        "CMbRedisMutex" => CMbRedisMutex::class,
        "CMbAPCMutex"   => CMbAPCMutex::class,
        "CMbFileMutex"  => CMbFileMutex::class,
    ];

    /** @var self[] */
    static $open_mutexes = [];

    /** @var CMbMutexDriver */
    private $driver;

    /** @var string Key */
    private $key;

    /**
     * @see parent::__construct()
     */
    function __construct($key, $label = null)
    {
        $this->key = $key;

        $driver = null;

        $config = CAppUI::conf("mutex_drivers");

        foreach (self::$drivers as $_driver_key => $_driver_class) {
            if (empty($config[$_driver_key])) {
                continue;
            }

            try {
                /** @var IMbMutex $driver */

                $driver = new $_driver_class($key, $label);

                break;
            } catch (Exception $e) {
                continue;
            }
        }

        if ($driver) {
            $this->driver = $driver;
        } else {
            throw new CMbException("No mutex driver available");
        }
    }

    /**
     * Get driver object
     *
     * @return CMbMutexDriver|IMbMutex
     */
    function getDriver()
    {
        return $this->driver;
    }

    /**
     * Get the mutext key
     *
     * @return string
     */
    function getKey()
    {
        return $this->key;
    }

    /**
     * @see parent::acquire()
     */
    function acquire($duration = self::DEFAULT_TIMEOUT, $poll_delay = self::DEFAULT_POLL_DELAY)
    {
        self::$open_mutexes[$this->key] = $this;

        return $this->driver->acquire($duration, $poll_delay);
    }

    /**
     * @see parent::lock()
     */
    function lock($duration = self::DEFAULT_TIMEOUT)
    {
        $lock_aquired = $this->driver->lock($duration);

        if ($lock_aquired) {
            self::$open_mutexes[$this->key] = $this;
        }

        return $lock_aquired;
    }

    /**
     * @see parent::release()
     */
    function release()
    {
        unset(self::$open_mutexes[$this->key]);

        $this->driver->release();
    }

    /**
     * Renders a failed acquisition message
     *
     * @return void
     */
    function failedMessage()
    {
        CAppUI::stepMessage(UI_MSG_OK, "CMbLock-failed-message", $this->getKey());
    }

    /**
     * Release all mutexes on script end
     *
     * @return void
     */
    static function releaseMutexes()
    {
        foreach (self::$open_mutexes as $_mutex) {
            trigger_error(sprintf("Mutex '%s' was not released properly", $_mutex->getKey()), E_USER_NOTICE);
            $_mutex->release();
        }
    }

    /**
     * Forgets a mutex. Do not automatically close it on script ending
     *
     * @return bool
     */
    function forget()
    {
        if ($this->key && isset(self::$open_mutexes[$this->key])) {
            unset(self::$open_mutexes[$this->key]);

            return true;
        }

        return false;
    }

    public static function getDistributedMutex($mutex_key)
    {
        $lock = new CMbMutex($mutex_key);

        if (CSessionHandler::getEngine() instanceof CRedisSessionHandler && !$lock->getDriver(
                ) instanceof CMbRedisMutex) {
            CApp::log('Invalid mutex driver configuration on redis session', LoggerLevels::LEVEL_ALERT);
            $lock->release();

            throw new Exception('Invalid mutex driver configuration on redis session');
        }

        return $lock;
    }
}
