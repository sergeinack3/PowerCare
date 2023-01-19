<?php
/**
 * @package Mediboard\Core\Sessions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Sessions;

/**
 * Session handler interface
 */
interface ISessionHandler {
  /**
   * Init the session handler
   *
   * @return bool
   */
  function init();

  /**
   * Check if this handler use user's functions
   *
   * @return bool
   */
  function useUserHandler();

  /**
   * Open the session
   *
   * @return bool
   */
  function open();

  /**
   * Close the session
   *
   * @return bool
   */
  function close();

  /**
   * Read the session
   *
   * @param string $session_id Session ID
   *
   * @return string Session data
   */
  function read($session_id);

  /**
   * Write the session
   *
   * @param string $id   Session ID
   * @param string $data Session data
   *
   * @return bool
   */
  function write($id, $data);

  /**
   * Destroy the session
   *
   * @param string $session_id Session ID
   *
   * @return bool
   */
  function destroy($session_id);

  /**
   * Garbage Collector
   *
   * @param int $max life time (sec.)
   *
   * @return bool
   */
  function gc($max);

  /**
   * List current sessions ids
   *
   * @return array of the ids
   */
  function listSessions();

  /**
   * Set life time
   *
   * @param int $lifetime The new life time in seconds
   *
   * @return void
   */
  function setLifeTime($lifetime);

  /**
   * Check wether the session exists or not
   *
   * @param string $session_id The session id
   *
   * @return bool
   */
  function exists($session_id);
}