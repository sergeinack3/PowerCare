<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Ox\Mediboard\Patients\CPatient;
use Ox\Core\CStoredObject;

/**
 * Userlog reference storing used to track objects' modifications
 */
class CPatientLog extends CStoredObject {
  /** @var integer Primary key */
  public $patient_log_id;

  /** @var int $user_log_id */
  public $user_log_id;

  /** @var int $patient_id */
  public $patient_id;

  /** @var CUserLog $_ref_userlog */
  public $_ref_userlog;
  /** @var CPatient $_ref_patient */
  public $_ref_patient;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->loggable = false;
    $spec->table = "patient_log";
    $spec->key   = "patient_log_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["user_log_id"] = "ref notNull class|CUserLog back|patient_logs";
    $props["patient_id"] = "ref notNull class|CPatient back|patient_logs";

    return $props;
  }

  /**
   * Load ref user log
   *
   * @return CStoredObject|null
   */
  function loadRefUserLog() {
    return $this->_ref_userlog = $this->loadFwdRef('user_log_id', true);
  }


  /**
   * Load ref user log
   *
   * @return CStoredObject|null
   */
  function loadRefPatient() {
    return $this->_ref_patient = $this->loadFwdRef('patient_id', true);
  }

  /**
   * Load all logs for given patient
   *
   * @param string $patient_id Patient id
   *
   * @return CPatientLog[]
   */
  function loadAllFor($patient_id) {
    $patient_log = new self();
    $ds = self::getDS();

    $where['patient_id'] = $ds->prepare('= ?', $patient_id);

    $logs = $patient_log->loadList($where, 'patient_log_id DESC');
    CStoredObject::massLoadFwdRef($logs, 'user_log_id');
    CStoredObject::massLoadFwdRef($logs, 'patient_id');
    /** @var CPatientLog $_log */
    foreach ($logs as $_log) {
      $_log->loadRefUserLog();
      $_log->_ref_userlog->loadTargetObject();
      $_log->_ref_userlog->loadRefUser();
      $_log->loadRefPatient();
    }

    return $logs;
  }

  /**
   * Count log for given patient id
   *
   * @param string $patient_id Patient id
   *
   * @return int
   */
  function count($patient_id) {
    $patient_log = new self();
    $ds = self::getDS();

    $where['patient_id'] = $ds->prepare('= ?', $patient_id);
    return $patient_log->countList($where);
  }
}