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
use Ox\Core\Mutex\CMbFileMutex;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\System\CRedisServer;

/**
 * Redis based session handler
 */
class CRedisSessionHandler implements ISessionHandler {
  /** @var string */
  private static $prefix;

  private $lock_name;
  private $lock_timeout = 30;

  private $lifetime;

  /** @var CMbMutex */
  private $mutex;

  /** @var string */
  private $data_hash;

  /**
   * @see parent::init()
   */
  function init() {
    global $dPconfig;

    // Must be the same here and in CApp
    // We don't use CApp because it can be called in /install
    $root_dir     = $dPconfig['root_dir'];
    $prefix       = preg_replace('/[^\w]+/', "_", $root_dir);
    self::$prefix = "$prefix-session-";

    if (PHP_VERSION_ID < 70200) {
      ini_set("session.save_handler", "user");
    }

    return true;
  }

  /**
   * Get dictionary key
   *
   * @param string $session_id Session ID
   *
   * @return string
   */
  private function getKey($session_id) {
    return self::$prefix . $session_id;
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
    return true;
  }

  /**
   * @see parent::close()
   */
  function close() {
    $this->mutex->release();

    return true;
  }

  /**
   * @see parent::read()
   */
  function read($session_id) {
    $client = CRedisServer::getClient();

    $this->lock_name = "session_$session_id";
    $this->lifetime  = CSessionHandler::getSessionMaxLifetime();

    // Init the right mutex type
    $mutex = new CMbFileMutex($this->lock_name);
    $mutex->acquire($this->lock_timeout);
    $this->mutex = $mutex;

    CSessionHandler::$acquire_end = microtime(true);

    $key = $this->getKey($session_id);

    if (!$client->has($key)) {
      return "";
    }

    $session = $client->get($key);

    if ($session) {
      $session = unserialize($session);
      $data    = $session['data'];

      $this->data_hash = md5($data);

      return $data;
    }

    return "";
  }

  /**
   * @see parent::write()
   */
  function write($session_id, $data) {
    if (!CAppUI::reviveSession()) {
      return false;
    }

    $client = CRedisServer::getClient();

    $address = CMbServer::getRemoteAddress();
    $user_id = CAppUI::$instance->user_id;
    $user_ip = $address["remote"] ? inet_pton($address["remote"]) : null;

    $new_hash = md5($data);

    $key = $this->getKey($session_id);

    // If session is to be updated
    if ($this->data_hash || $this->data_hash !== $new_hash) {
      $session = array(
        "user_id" => $user_id,
        "user_ip" => $user_ip,
        "data"    => $data,
      );

      $client->set($key, serialize($session), $this->lifetime);
    }
    else {
      $client->expire($key, $this->lifetime);
    }

    return true;
  }

  /**
   * @see parent::destroy()
   */
  function destroy($session_id) {
    $key = $this->getKey($session_id);

    return CRedisServer::getClient()->remove($key);
  }

  /**
   * @see parent::gc()
   */
  function gc($max) {
    // TTL is here for this ...
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
    $key = $this->getKey($session_id);

    return CRedisServer::getClient()->has($key);
  }
}
