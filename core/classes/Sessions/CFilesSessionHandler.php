<?php
/**
 * @package Mediboard\Core\Sessions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Sessions;

/**
 * File based Session Handler
 */
class CFilesSessionHandler implements ISessionHandler {
  /**
   * @see parent::init()
   */
  function init() {
    return ini_set("session.save_handler", "files");
  }

  /**
   * @see parent::useUserHandler()
   */
  function useUserHandler() {
    return false;
  }

  /**
   * @see parent::open()
   */
  function open() {
    return false;
  }

  /**
   * @see parent::close()
   */
  function close() {
    return false;
  }

  /**
   * @see parent::read()
   */
  function read($session_id) {
    return false;
  }

  /**
   * @see parent::write()
   */
  function write($id, $data) {
    return false;
  }

  /**
   * @inheritdoc
   */
  function destroy($session_id) {
    $session_path = session_save_path() . "/sess_$session_id";
    if (is_file($session_path)) {
      return unlink($session_path);
    }
    return false;
  }

  /**
   * @see parent::gc()
   */
  function gc($max) {
    return false;
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
  }

  /**
   * @inheritdoc
   */
  function exists($session_id) {
    return is_file(session_save_path() . "/sess_$session_id");
  }
}