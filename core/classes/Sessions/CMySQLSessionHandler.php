<?php
/**
 * @package Mediboard\Core\Sessions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Sessions;

use Ox\Core\CAppUI;
use Ox\Core\CMbServer;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CSQLDataSource;
use Ox\Core\Mutex\CMbFileMutex;
use Ox\Core\Mutex\CMbMutex;

/**
 * MySQL based session handler
 */
class CMySQLSessionHandler implements ISessionHandler {
  const DATA_CHANGE_TIME = 5; // in seconds

  /** @var CPDOMySQLDataSource */
  private static $ds;

  private $lock_name;
  private $lock_timeout = 30;

  private $lifetime; // From ini file
  private $expire;   // expire info from the session

  private $mutex_type;

  /** @var CMbMutex */
  private $mutex;

  /** @var string */
  private $data_hash;

  /**
   * @see parent::init()
   */
  function init() {
    $this->mutex_type = @CAppUI::conf("session_handler_mutex_type");

    if (PHP_VERSION_ID < 70200) {
      ini_set("session.save_handler", "user");
    }

    return true;
  }

  /**
   * @see parent::useUserHandler()
   */
  function useUserHandler() {
    return true;
  }

  /**
   * @see parent::open()
   */
  function open() {
    if (self::$ds = CSQLDataSource::get("std")) {
      return true;
    }

    return false;
  }

  /**
   * @see parent::close()
   */
  function close() {
    if ($this->mutex) {
      $this->mutex->release();
    }
    else {
      $ds    = self::$ds;
      $query = $ds->prepare("SELECT RELEASE_LOCK(%1)", $this->lock_name);

      if (!$ds->query($query)) {
        return false;
      }
    }

    return true;
  }

  /**
   * @see parent::read()
   */
  function read($session_id) {
    $ds = self::$ds;

    $this->lock_name = "session_$session_id";
    $this->lifetime  = CSessionHandler::getSessionMaxLifetime();

    // Init the right mutex type
    $mutex = null;
    switch ($this->mutex_type) {
      case "files":
        $mutex = new CMbFileMutex($this->lock_name);
        break;

      case "system":
        $mutex = new CMbMutex($this->lock_name);
        break;

      default:
        $query = $ds->prepare("SELECT GET_LOCK(%1, %2)", $this->lock_name, $this->lock_timeout);
        $ds->query($query);
        break;
    }

    if ($mutex) {
      $mutex->acquire($this->lock_timeout);
      $this->mutex = $mutex;
    }

    CSessionHandler::$acquire_end = microtime(true);

    $query  = $ds->prepare("SELECT `data`, `expire` FROM `session` WHERE `session_id` = ?1 AND `expire` > ?2;", $session_id, time());
    $result = $ds->exec($query);

    if ($record = $ds->fetchAssoc($result)) {
      $this->expire = $record['expire'];
      $data         = $record['data'];

      $new_data = @gzuncompress($data ?? '');
      if ($new_data) {
        $data = $new_data;
      }

      $this->data_hash = md5($data);

      return $data;
    }

    $ds->freeResult($result);

    return '';
  }

  /**
   * @see parent::write()
   */
  function write($session_id, $data) {
    if (!CAppUI::reviveSession()) {
      return false;
    }

    $ds = self::$ds;

    $address = CMbServer::getRemoteAddress();
    $user_id = CAppUI::$instance->user_id;
    $user_ip = $address["remote"] ? inet_pton($address["remote"]) : null;
    $expire  = time() + $this->lifetime;

    // If session is to be updated
    if ($this->data_hash) {
      $new_hash = md5($data);
      if ($this->data_hash !== $new_hash) {
        $compressed_data = gzcompress($data);
        $query           = "UPDATE `session` SET `user_id` = ?1, `user_ip` = ?2, `expire` = ?3, `data` = ?4 WHERE `session_id` = ?5;";
        $query           = $ds->prepare($query, $user_id, $user_ip, $expire, $compressed_data, $session_id);
      }
      else {
        // If session was already written less than X seconds, don't update it once again
        if ($expire - $this->expire < self::DATA_CHANGE_TIME) {
          return true;
        }

        $query = "UPDATE `session` SET `user_id` = ?1, `user_ip` = ?2, `expire` = ?3 WHERE `session_id` = ?4;";
        $query = $ds->prepare($query, $user_id, $user_ip, $expire, $session_id);
      }
    }

    // No session yet
    else {
      $compressed_data = gzcompress($data);
      $query           = "INSERT INTO `session` (`session_id`, `user_id`, `user_ip`, `expire`, `data`)
                VALUES (?1, ?2, ?3, ?4, ?5) ON DUPLICATE KEY UPDATE `data` = ?6, `expire` = ?7;";
      $query           = $ds->prepare($query, $session_id, $user_id, $user_ip, $expire, $compressed_data, $compressed_data, $expire);
    }

    if (!$ds->query($query)) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function destroy($session_id) {
    $ds = self::$ds;

    $query = $ds->prepare("DELETE FROM `session` WHERE `session_id` = ?;", $session_id);
    $ds->exec($query);

    return $ds->affectedRows() > 0;
  }

  /**
   * @see parent::gc()
   */
  function gc($max) {
    $ds = self::$ds;

    $query = $ds->prepare("DELETE FROM `session` WHERE `expire` < ?;", time());

    if (!$ds->query($query)) {
      return false;
    }

    return true;
  }

  /**
   * @see parent::listSessions()
   */
  function listSessions() {
    return array();
  }

  /**
   * @see parent::setLifeTime()
   */
  function setLifeTime($lifetime) {
    $this->lifetime = $lifetime;
  }

  /**
   * @inheritdoc
   */
  function exists($session_id) {
    $ds = self::$ds;

    $query = $ds->prepare("SELECT COUNT(*) FROM `session` WHERE `session_id` = ?1 AND `expire` > ?2;", $session_id, time());

    return $ds->loadResult($query);
  }
}
