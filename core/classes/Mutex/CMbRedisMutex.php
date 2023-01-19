<?php
/**
 * @package Mediboard\Core\Mutex
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Mutex;

use Ox\Core\Redis\CRedisClient;
use Ox\Mediboard\System\CRedisServer;

/**
 * Semaphore implementation to deal with concurrency
 */
class CMbRedisMutex extends CMbMutexDriver {
  /** @var CRedisClient */
  private $client;

  /**
   * @see parent::__construct()
   */
  function __construct($key, $label = null) {
    parent::__construct($key, $label);

    $this->client = CRedisServer::getClient();
  }

  /**
   * @see parent::release()
   */
  public function release(){
    if ($this->canRelease()) {
      $this->client->remove($this->getLockKey());
    }
  }

  /**
   * @see parent::setLock()
   */
  protected function setLock($duration){
    $key = $this->getLockKey();
    $tmp_key = uniqid("$key-");
    $client = $this->client;

    $client->multi(); // Start

    $client->setNX($tmp_key, 1);
    $client->expire($tmp_key, $duration);
    $client->renameNX($tmp_key, $key);
    $client->remove($tmp_key); // GC, if rename failed

    $ret = $client->exec();  // End

    return $ret[2] == 1; // renameNX result
  }

  /**
   * Never has to recover as keys are volatile
   *
   * @see parent::recover()
   */
  protected function recover($duration){
    return false;
  }
}
