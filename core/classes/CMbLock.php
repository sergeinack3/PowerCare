<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Manage locking files to deal with concurrency
 */
class CMbLock {
  public $key;
  public $process;
  public $path;
  public $filename;

  /**
   * Construct
   *
   * @param string $key lock identifier
   */
  function __construct($key) {
    $this->path = CAppUI::conf("root_dir")."/tmp/locks";
    $this->process = getmypid();

    $prefix = CApp::getAppIdentifier();
    $this->key = "$prefix-lock-$key";

    $this->filename = "$this->path/$this->key";
    CMbPath::forceDir(dirname($this->filename));
  }

  /**
   * Try to acquire a lock file
   *
   * @param float $lock_lifetime The lock life time in seconds
   *
   * @return bool
   */
  function acquire($lock_lifetime = 300.0) {
    // No lock, we acquire
    clearstatcache(true, $this->filename);
    if (!file_exists($this->filename)) {
      return touch($this->filename);
    }

    // File exists, we have to check lifetime
    $lock_mtime = filemtime($this->filename);

    // Lock file is not dead
    if ( (microtime(true) - $lock_mtime) <= $lock_lifetime ) {
      return false;
    }

    // Lock file too old
    $this->release();

    return $this->acquire($lock_lifetime);
  }

  /**
   * Release (delete) a lock file
   *
   * @return bool
   */
  function release() {
    clearstatcache(true, $this->filename);
    if (file_exists($this->filename)) {
      return unlink($this->filename);
    }

    return true;
  }

  /**
   * Renders a failed acquisition message
   *
   * @return void
   */
  function failedMessage() {
    CAppUI::stepMessage(UI_MSG_OK, "CMbLock-failed-message", $this->key);
  }
}
