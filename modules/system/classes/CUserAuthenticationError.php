<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\Admin\CUser;

/**
 * User authentication error
 */
class CUserAuthenticationError extends CMbObject {
  public $user_authentication_error_id;

  public $user_id;
  public $login_value;
  public $datetime;
  public $auth_method;
  public $identifier;
  public $ip_address;
  public $message;

  /** @var CUser */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = "user_authentication_error";
    $spec->key      = "user_authentication_error_id";
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                = parent::getProps();
    $props["user_id"]     = "ref class|CUser back|authentication_errors";
    $props["login_value"] = "str notNull";
    $props["datetime"]    = "dateTime notNull";
    $props["auth_method"] = "enum list|" . implode('|', CUserAuthentication::AUTH_METHODS);
    $props["identifier"]  = "str notNull";
    $props["ip_address"]  = "str notNull";
    $props["message"]     = "text";

    return $props;
  }

  /**
   * Get user
   *
   * @return CUser
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef("user_id", true);
  }


  /**
   * Get user's current authentication ID
   *
   * @return int
   */
  static function makeIdentifier() {
    $chars = array_merge(range('A', 'F'), range(0, 9));

    return CMbSecurity::getRandomAlphaNumericString($chars, 16);
  }

  /**
   * Logs a login attempt error
   *
   * @param string $login       Login
   * @param int    $user_id     User ID, if any
   * @param string $auth_method Authentication method
   * @param string $message     The Authentication error message
   *
   * @return CUserAuthenticationError
   */
  static function logError($login, $user_id, $auth_method = null, $message = null) {
    $app = CAppUI::$instance;

    $error              = new static();
    $error->login_value = $login;
    $error->user_id     = $user_id;
    $error->message     = !is_null($message) ? $message : CAppUI::getMsg(false);
    $error->datetime    = CMbDT::dateTime();
    $error->auth_method = $auth_method;
    $error->ip_address  = $app->ip;
    $error->identifier  = self::makeIdentifier();

    $error->store();

    return $error;
  }
}
