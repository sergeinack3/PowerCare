<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Cron;

use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Description
 */
class CCronJobExecution implements IShortNameAutoloadable {
  /** @var CCronJob */
  private $job;

  /** @var CCronJobLog */
  private $log_id;

  /** @var resource */
  private $handle;

  /**
   * CCronJobExecution constructor.
   *
   * @param CCronJob $job
   * @param int      $log_id
   * @param resource $handle
   */
  public function __construct(CCronJob $job, $log_id, $handle) {
    $this->job    = $job;
    $this->log_id = $log_id;
    $this->handle = $handle;
  }

  /**
   * @return CCronJob
   */
  public function getJob() {
    return $this->job;
  }

  /**
   * @return int
   */
  public function getLog() {
    return $this->log_id;
  }

  /**
   * @return resource
   */
  public function getHandle() {
    return $this->handle;
  }

  /**
   * Get the HTTP info for the handle
   *
   * @return mixed
   */
  public function getHttpInfo() {
    return curl_getinfo($this->handle);
  }
}