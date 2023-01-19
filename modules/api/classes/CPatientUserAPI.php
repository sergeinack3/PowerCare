<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
class CPatientUserAPI extends CStoredObject {
  /** @var array */
  static public $_ref_concordance = array(
    "CFitbitAPI"       => "CUserAPIFitbit",
    "CWithingsAPI"     => "CUserAPIWithings",
    "CUserAPIFitbit"   => "CFitbitAPI",
    "CUserAPIWithings" => "CWithingsAPI"
  );

  //db field
  /** @var integer Primary key */
  public $patient_user_api_id;
  // clé primaire de CUserAPI
  public $patient_id;
  public $api_user_id;
  public $api_user_class;
  public $first_call;
  public $last_call;

  //ref
  public $synchronized_since;
  /** @var CUserAPI $_user_api */
  public $_user_api;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "patient_user_api";
    $spec->key   = "patient_user_api_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                       = parent::getProps();
    $props["patient_id"]         = "ref class|CPatient autocomplete|_view notNull seekable back|patient_user_api";
    $props["first_call"]         = "dateTime notNull";
    $props["last_call"]          = "dateTime notNull";
    $props["synchronized_since"] = "date";
    $props["api_user_class"]     = "enum list|" . implode("|", CPatientUserAPI::getAPiUserClass(CAPITiers::getAPIList())) . " notNull";
    $props["api_user_id"]        = "ref meta|api_user_class notNull back|patient_user_api";

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function store() {
    if (!$this->_id) {
      $this->first_call = CMbDT::dateTime();
      $this->last_call  = CMbDT::dateTime();
    }
    else {
      $this->last_call = CMbDT::dateTime();
    }

    return parent::store();
  }

  /**
   * Check if patient is not synchronized with api
   *
   * @param CPatient $patient   patient
   * @param String   $api_class api class name
   *
   * @return boolean
   */
  public static function checkPatientNotSynchronized(CPatient $patient, $api_class) {
    $patient_api = $patient->loadRefPatientUserAPI(array("api_user_class" => "= '" . CPatientUserAPI::getAPiUserClass($api_class) . "'"));
    if ($patient_api->_id) {
      return false;
    }

    return true;
  }

  /**
   * Get name of class associate to api
   *
   * @param String|array $class_api_tiers name of api
   *
   * @return mixed
   */
  public static function getAPiUserClass($class_api_tiers) {
    if (is_array($class_api_tiers)) {
      $res = array();
      foreach ($class_api_tiers as $_class) {
        $res[] = CPatientUserAPI::getAPiUserClass($_class);
      }

      return $res;
    }

    return CMbArray::get(self::$_ref_concordance, "$class_api_tiers");
  }

  /**
   * Load target object, user api
   *
   * @return CUserAPIOAuth|CStoredObject
   */
  function loadTargetObject() {
    /** @var CUserAPIOAuth $api */
    $this->_user_api = new $this->api_user_class;

    $where = array(
      "user_id" => "= '$this->api_user_id'",
      "active"  => "= '1'"
    );
    if (!$this->_user_api->loadObject($where)) {
      return null;
    }

    return $this->_user_api;
  }
}
