<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\CStoredObject;

/**
 * Description
 */
class CUserAPI extends CStoredObject {
  /** @var integer Primary key */
  public $user_id;

  // identifiant user de l'api (clé de l'api)
  public $user_api_id;
  public $scope_accepted;
  public $constant_accepted;
  public $obj_weight;
  public $obj_steps;
  public $active;
  public $created_date_api;

  public $_vw_obj_weight;


  /**
   * @inheritdoc
   */
  public function getSpec() {
    $spec      = parent::getSpec();
    $spec->key = "user_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  public function getProps() {
    $props                      = parent::getProps();
    $props["user_api_id"]       = "str notNull";
    $props["scope_accepted"]    = "str";
    $props["constant_accepted"] = "str";
    $props["obj_weight"]        = "num";
    $props["obj_steps"]         = "num max|1000000";
    $props["active"]            = "bool notNull";
    $props["created_date_api"]  = "date";

    return $props;
  }

  /**
   * @inheritdoc
   */
  public function updateFormFields() {
    parent::updateFormFields();
    if ($this->obj_weight) {
      $this->_vw_obj_weight = number_format($this->obj_weight / 1000, 1);
    }
  }

  /**
   * Get goals for user
   *
   * @return array
   */
  static function getGoals() {
    return array("obj_weight", "obj_steps");
  }

  /**
   * Get users synchronise for patient
   *
   * @param int $patient_id patient id
   *
   * @return CUserAPI[]
   * @throws Exception
   */
  public static function getUsersAPIs($patient_id) {
    $users         = array();
    $where         = array("patient_id" => "= '$patient_id'");
    $patient_api   = new CPatientUserAPI();
    $patients_apis = $patient_api->loadList($where);
    if (!$patients_apis) {
      return array();
    }
    foreach ($patients_apis as $_patient_api) {
      /** @var CUserAPI $user_api */
      $user_api = $_patient_api->loadTargetObject();
      if (!$user_api) {
        continue;
      }
      $users[CPatientUserAPI::getAPiUserClass($user_api->_class)] = $user_api;
    }

    return $users;
  }

  /**
   * Get accepted constants as an array
   *
   * @return array
   */
  public function getAcceptedConstantAsArray() {
    if (!$this->constant_accepted) {
      return array();
    }

    $requests_names = array();
    $exploded = explode(" ", $this->constant_accepted);
    foreach ($exploded as $_request_name) {
      switch ($_request_name) {
        case "hourlyactivity" :
        case "dailyactivity" :
        case CAPITiers::_REQUEST_ACTIVITY_HOURLY :
        case CAPITiers::_REQUEST_ACTIVITY_DAILY :
          $requests_names[CAPITiers::REQUEST_ACTIVITY] = CAPITiers::REQUEST_ACTIVITY;
          break;
        case "hourlysleep" :
        case "dailysleep" :
        case CAPITiers::_REQUEST_SLEEP_HOURLY :
        case CAPITiers::_REQUEST_SLEEP_DAILY :
          $requests_names[CAPITiers::REQUEST_SLEEP] = CAPITiers::REQUEST_SLEEP;
          break;
        default:
          $requests_names[$_request_name] = $_request_name;
      }
    }

    return array_values($requests_names);
  }

  /**
   * Load patient user api
   * 
   * @return CPatientUserAPI
   * @throws Exception
   */
  public function loadRefPatientApi() {
    $patient_api = new CPatientUserAPI();
    $patient_api->api_user_class = $this->_class;
    $patient_api->api_user_id = $this->_id;
    $patient_api->loadMatchingObject();
    
    return $patient_api;
  }

  /**
   * Format accepted scope in array
   *
   * @return array
   */
  public function getAcceptedScopeToArray() {
    if (!$this->scope_accepted) {
      return array();
    }

    return explode(" ", $this->scope_accepted);
  }

  /**
   * Get api tool
   *
   * @return CAPITiers
   */
  public function getAPITool() {
    $classe_name = CPatientUserAPI::getAPiUserClass($this->_class);

    return new $classe_name;
  }
}