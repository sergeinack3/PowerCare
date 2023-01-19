<?php
/**
 * @package Mediboard\Core\Mutex
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Mutex;

/**
 * APC & APCU mutex handler
 */
class CMbAPCMutex extends CMbMutexDriver
{
    static protected $_slam_defense = null;

    protected $_error;

    /** @var bool */
    private $apcu_mode = false;


    private function is_enabled(): bool
    {
        if (function_exists("apcu_enabled") && apcu_enabled()) {
            $this->apcu_mode = true;

            return true;
        }

        if (function_exists("apc_exists")) {
            return true;
        }

        return false;
    }

    /**
     * @see parent::__construct()
     */
    public function __construct($key, $label = null)
    {
        if (!$this->is_enabled()) {
            throw new \Exception("APC unavailable");
        }

        if (ini_get("apc.slam_defense")) {
            throw new \Exception("APC available, but slam defense should be disabled");
        }

        parent::__construct($key, $label);
    }

    /**
     * @see parent::release()
     */
    public function release()
    {
        if ($this->canRelease()) {
            $this->apcu_mode ? apcu_delete($this->getLockKey()) : apc_delete($this->getLockKey());
        }
    }

    /**
     * @see parent::setLock()
     */
    protected function setLock($duration)
    {
        $added = $this->apcu_mode ? @apcu_add($this->getLockKey(), 1, $duration) :
            @apc_add($this->getLockKey(), 1, $duration);
        return (bool)$added;
    }

    /**
     * Never has to recover as keys are volatile
     *
     * @see parent::recover()
     */
    protected function recover($duration) {
        return false;
    }
}
