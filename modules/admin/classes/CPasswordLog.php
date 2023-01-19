<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;

/**
 * Description
 */
class CPasswordLog extends CMbObject {
  /** @var integer Primary key */
  public $password_log_id;

  /** @var integer User ID */
  public $user_id;

  /** @var string Password date */
  public $password_date;

  /** @var string Password salt */
  public $password_salt;

  /** @var string Password hash */
  public $password_hash;

  /** @var CUser */
  public $_ref_user;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec           = parent::getSpec();
    $spec->table    = 'password_log';
    $spec->key      = 'password_log_id';
    $spec->loggable = false;

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                  = parent::getProps();
    $props['user_id']       = 'ref class|CUser notNull cascade back|password_logs';
    $props['password_date'] = 'date notNull';
    $props['password_salt'] = 'str maxLength|64 notNull show|0 loggable|0';
    $props['password_hash'] = 'str maxLength|64 notNull show|0 loggable|0';

    return $props;
  }

  /**
   * Logs a used password
   *
   * @param string  $password_salt Password salt
   * @param string  $password_hash Password hash
   * @param integer $user_id       User ID
   * @param string  $date          Password date
   *
   * @return mixed
   */
  static function logPassword($password_salt, $password_hash, $user_id, $date = null) {
    $log = new static();

    if (!$log->isInstalled()) {
      return;
    }

    $date = ($date) ?: CMbDT::date();

    $log->user_id       = $user_id;
    $log->password_date = $date;
    $log->password_salt = $password_salt;
    $log->password_hash = $password_hash;

    return $log->store();
  }

  /**
   * Loads related user
   *
   * @return CUser|null
   */
  function loadRefUser() {
    return $this->_ref_user = $this->loadFwdRef('user_id', true);
  }

  /**
   * Checks if given password setting is allowed for given user
   *
   * @param string  $password Password to test
   * @param integer $user_id  User ID
   *
   * @return bool
   */
  static function isPasswordAllowed($password, $user_id) {
    if (!$user_id) {
      return true;
    }

    $probation_period = CAppUI::conf('admin CUser reuse_password_probation_period');
    $probation_period = ($probation_period) ?: 'none';

    if ($probation_period === 'none') {
      return true;
    }

    $old_password = new static();

    if (!$old_password->isInstalled()) {
      return true;
    }

    $ds = $old_password->getDS();

    $where = array(
      'user_id' => $ds->prepare('= ?', $user_id),
    );

    if ($probation_period !== 'never') {
      if (preg_match('/(?P<number>\d+)-(?P<period>day|week|month|year)/', $probation_period, $matches)) {
        $limit                  = CMbDT::date("-{$matches['number']} {$matches['period']}");
        $where['password_date'] = $ds->prepare('> ?', $limit);
      }
      else {
        return false;
      }
    }

    $old_passwords = $old_password->loadList($where);

    foreach ($old_passwords as $_old_password) {
      if (hash('SHA256', $_old_password->password_salt . $password) === $_old_password->password_hash) {
        return false;
      }
    }

    return true;
  }
}
