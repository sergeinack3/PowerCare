<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\Constants\CActionReport;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CSourceHTTP;
use ReflectionException;

/**
 * Description
 */
abstract class CAPITiers implements IAPITiers, IShortNameAutoloadable {
  const REQUEST_WEIGHT           = "weight";
  const REQUEST_SLEEP            = "sleep";
  const _REQUEST_SLEEP_DAILY     = "dailysleep";
  const _REQUEST_SLEEP_HOURLY    = "hourlysleep";
  const REQUEST_ARTERIAL_PRESURE = "arterialPressure";
  const REQUEST_ACTIVITY         = "activity";
  const _REQUEST_ACTIVITY_HOURLY = "hourlyactivity";
  const _REQUEST_ACTIVITY_DAILY  = "dailyactivity";
  const REQUEST_HEARTRATE        = "heartrate";
  const REQUEST_HEIGHT           = "height";
  const _REQUEST_DISTANCE        = "distance";

  // format_date
  const DATE_ISO            = 0;
  const DATETIME_ISO        = 1;
  const TIMESTAMPS          = 2;
  const ROUND_DAY_TIMETAMPS = 3;
  const NO_DATE             = 4;
  const UNIQUE_DATE_ISO     = 5;
  const UNIQUE_DATETIME_ISO = 6;
  const MAX_NUMBER_ATTEMP   = 5;

  const API_TIERS = ["CWithingsAPI", "CFitbitAPI"];

  public static $CONFLICT_ACCOUNT_ID = 1;

  // config //
  public $urlCallBack;
  public $name_api;
  public $supported_constants;

  // config a redéfinir dans les class concrète //
  public $_class;
  public $_limit_application = false;
  public $id_client;
  public $id_secret;
  public $scope; // les scope gérés
  public $_constant_data;  // les constantes gérées
  public $collection_notifications;
  // Fin des config //

  // data //
  public $requests_names; // constant géré
  public $params;
  public $request_id;
  /** @var $patient_api CPatientUserAPI */
  public $patient_api;
  /** @var $user_api CUserAPI */
  public $user_api;
  /** @var $patient CPatient */
  public $patient;
  public $_constants_authorizations;
  public $_warning_account_conflitct_id;
  public $_warning_conf;

  // Contient les constantes autorisées séparées par des " "
  public $_warning_account_conflitct = false;
  // informations sur les conflicts
  public $_response_code;
  public $_reload_request = false;
  // http response code from API
  public $_acquittement;
  // besoin de refaire la requête
  public $_datetime_send;
  public $_datetime_received;
  public $_time_response;
  public $_available_get_data = false;
  private $date_min;

  // est-on en train de récupérer des données
  private $date_max;

  /**
   * CAPITiers constructor.
   */
  public function __construct() {
    $this->_class          = CClassMap::getSN($this);
    $this->urlCallBack     = CAppUI::conf("api url_callback");
    $this->params          = array();
  }

  /**
   * Get spec code from state for hourlysleep
   *
   * @param int $state state
   *
   * @return mixed
   */
  public static function getSpecFromState($state) {
    return self::_getSpecFromState($state);
  }

  /**
   * Get spec code from state or get state from spec code for hourlysleep
   *
   * @param int  $state   state of hourlysleep
   * @param bool $reverse reverse array to transform in spec => states
   *
   * @return mixed
   */
  private static function _getSpecFromState($state, $reverse = false) {
    $states = array(
      0 => "wakeupduration",
      1 => "lightsleepduration",
      2 => "deepsleepduration",
      3 => "remduration",
    );

    if ($reverse) {
      return CMbArray::getRecursive(CMbArray::flip($states), "$state 0");
    }

    return CMbArray::get($states, $state);
  }

  /**
   * Get state from spec code for hourlysleep
   *
   * @param string $spec spec code
   *
   * @return mixed
   */
  public static function getStateFromSpec($spec) {
    return self::_getSpecFromState($spec, true);
  }

  /**
   * Treat request in stackRequest
   *
   * @param CAPITiersStackRequest[] $requests requests
   * @param CAPITiers               $api      CFitbitAPI | CWithingsAPI
   * @param int                     $page     load more requests if user is locked
   *
   * @return void
   * @throws CAPITiersException
   */
  public static function treatStack($requests, $api, $page = 0) {
    $user_id_limited = array();
    /** @var CAPITiersStackRequest $_request */
    foreach ($requests as $_request) {
      try {
        if (CMbArray::in($_request->api_id, $user_id_limited)) {
          continue;
        }
        $_request->treatRequest($api);
      }
      catch (CAPITiersException $exception) {
        $code = $exception->getCode();
        if ($code === CAPITiersException::TOO_MANY_REQUEST) {
          if ($api->_limit_application) {
            break;
          }
          $user_id_limited[] = $_request->api_id;
          continue;
        }

        if ($code === CAPITiersException::INVALID_TOKEN) {
          try {
            $api->refreshToken();
            $_request->treatRequest($api);
            continue;
          }
          catch (CAPITiersException $ex) {
            if ($ex->getCode() == CAPITiersException::INVALID_TOKEN || $ex->getCode() == CAPITiersException::INVALID_REFRESH_TOKEN) {
              $_request->delete();
              $api->deleteUser();
              continue;
            }
          }
        }
        $_request->max_attemp++;
        if ($_request->max_attemp > self::MAX_NUMBER_ATTEMP) {
          $_request->delete();
        }
        else {
          $_request->store();
        }
      }
    }
    $nb_request_failed = count($user_id_limited);
    if ($nb_request_failed < 5) {
      return;
    }
    $where    = array(
      "receive_datetime" => "IS NULL",
      "send_datetime"    => "IS NULL",
      "api_class"        => "= '" . CPatientUserAPI::getAPiUserClass($api->_class) . "'"
    );
    $number_req    = CAppUI::conf("api number_request");
    $page     += $nb_request_failed;
    $stack    = new CAPITiersStackRequest();
    $requests = $stack->loadList($where, null, "$page,$number_req");
    self::treatStack($requests, $api, $page);
  }

  /**
   * @inheritdoc
   */
  public function deleteUser() {
    if (!$this->patient_api) {
      $patient_user_api                 = new CPatientUserAPI();
      $patient_user_api->api_user_class = $this->user_api->_class;
      $patient_user_api->api_user_id    = $this->user_api->_id;
      $patient_user_api->loadMatchingObject();
      $this->setPatientUserAPI($patient_user_api);
    }
    $this->user_api->active = 0;

    if ($msg = $this->user_api->store()) {
      throw new CAPITiersException(CAPITiersException::INVALID_DELETE_USER_API);
    }

    if ($msg = $this->patient_api->delete()) {
      throw new CAPITiersException(CAPITiersException::INVALID_DELETE_PATIENT_USER_API, $msg);
    }
  }

  /**
   * @inheritdoc
   */
  public function setPatientUserAPI($patient_user_api) {
    if (!$patient_user_api || !$patient_user_api->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_USER_API);
    }
    $this->patient_api = $patient_user_api;
  }

  /**
   * @inheritDoc
   */
  public function setPatient($patient) {
    if (!$patient || !$patient->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_PATIENT);
    }
    $this->patient = $patient;
  }

  /**
   * Get list APIs
   *
   * @return array APIs name list
   * @throws ReflectionException
   */
  static function getAPIList() {
    return self::API_TIERS;
  }

  /**
   * Get group id for the context
   *
   * @return int
   * @throws Exception
   */
  public function getGroupId() {
    if ($this->patient) {
      return $this->patient->loadRefFirstPatientUser()->group_id;
    }

    if ($this->patient_api) {
      $patient = new CPatient();
      $patient->load($this->patient_api->patient_id);
      return $patient->loadRefFirstPatientUser()->group_id;
    }

    return CGroups::loadCurrent()->_id;
  }

  /**
   * Get created date in API or null if not possible
   *
   * @return string|null
   * @throws CAPITiersException
   */
  public abstract function getCreatedDateAPI();

  /**
   * Load conf API
   *
   * @param int $group_id current groupd id
   *
   * @return void
   * @throws CAPITiersException
   */
  public abstract function loadConf($group_id);

  /**
   * Format response scope
   *
   * @param String $scope scope to format
   *
   * @return String
   */
  abstract function formatAcceptedScope($scope);

  /**
   * Format scope to send to API
   *
   * @param array $scope scope to format
   *
   * @return String
   */
  abstract function formatScopeToSend($scope);

  /**
   * Treat error send from API
   *
   * @param mixed $error error return by API
   *
   * @return void
   * @throws CAPITiersException
   */
  abstract function treatError($error);

  /**
   * Know if error was returned from API
   *
   * @param array $response response from API
   *
   * @return mixed
   */
  abstract function hasError($response);

  /**
   * Prepare notification send by API
   *
   * @param array $params parameters send by API
   *
   * @return CAPITiersStackRequest[]
   * @throws CAPITiersException
   */
  abstract function prepareNotification($params);

  /**
   * Get subscriptions of user
   *
   * @param array $subscriptions subcription to view
   *
   * @return mixed
   * @throws CAPITiersException
   */
  abstract function getSubscriptions($subscriptions = array());

  /**
   * Verify if subscription active match with subscription wanted
   *
   * @return mixed
   * @throws CAPITiersException
   */
  abstract function verifySubscription();

  /**
   * @inheritdoc
   */
  public function hasConflict($type_conflict) {
    switch ($type_conflict) {
      case self::$CONFLICT_ACCOUNT_ID:
        return $this->_warning_account_conflitct;
      default:
        return false;
    }
  }

  /**
   * @inheritdoc
   */
  public function getConflict($type_conflict) {
    switch ($type_conflict) {
      case self::$CONFLICT_ACCOUNT_ID:
        return $this->_warning_account_conflitct_id;
      default:
        return false;
    }
  }

  /**
   * Get max interval day authorized for request
   *
   * @param String $request_name constant code
   *
   * @return String
   */
  public function getLimitDayForRequest($request_name) {
    return $this->getConstantData($request_name, "maxInterval");
  }

  /**
   * Get data for constant code
   *
   * @param String $request_name constant code
   * @param String $key           key of array
   *
   * @return String
   */
  private function getConstantData($request_name, $key) {
    return CMbArray::get(CMbArray::get($this->_constant_data, $request_name), $key);
  }

  /**
   * Get User API name
   *
   * @return String
   */
  public function getUserAPIName() {
    return CPatientUserAPI::getAPiUserClass($this->getClass());
  }

  /**
   * @inheritdoc
   */
  public function getClass() {
    return $this->_class;
  }

  /**
   * User account conflict
   *
   * @param int $user_id user api id from api
   *
   * @return void
   */
  public function setAccountConflict($user_id) {
    $this->_warning_account_conflitct    = true;
    $this->_warning_account_conflitct_id = $user_id;
  }

  /**
   * Set access to array for data
   *
   * @param String $path path to data
   *
   * @return void
   */
  public function setAccessBodyRequest($path) {
    $this->params["access_body"] = $path;
  }

  /**
   * Set key of data wanted
   *
   * @param array  $array         array
   * @param String $constant_code constant name
   *
   * @return void
   */
  public function setDataToRecover($array, $constant_code) {

    $this->params["data"]["$constant_code"] = $array;
  }

  /**
   * Initialize params date for api
   *
   * @param String $first_datetime End datetime
   * @param String $end_datetime   first datetime
   *
   * @return void
   */
  public function initializeIntervalDate($first_datetime, $end_datetime) {
    $this->date_min           = $first_datetime;
    $this->date_max           = $end_datetime;
    $this->params["date_min"] = $first_datetime;
    $this->params["date_max"] = $end_datetime;
  }

  /**
   * Verify requests names
   *
   * @param array $resquests resquests names
   */
  public static function verifyRequests($resquests) {
    $result = array();
    foreach ($resquests as $_request_name) {
      switch ($_request_name) {
        case "hourlyactivity":
        $result[] = self::_REQUEST_ACTIVITY_HOURLY;
        break;
        case "dailyactivity":
          $result[] = self::_REQUEST_ACTIVITY_DAILY;
          break;
        case self::REQUEST_ACTIVITY:
          $result[] = self::_REQUEST_ACTIVITY_DAILY;
          $result[] = self::_REQUEST_ACTIVITY_HOURLY;
          break;
        case "hourlysleep":
          $result[] = self::_REQUEST_SLEEP_HOURLY;
          break;
        case "dailysleep":
          $result[] = self::_REQUEST_SLEEP_DAILY;
          break;
        case self::REQUEST_SLEEP:
          $result[] = self::_REQUEST_SLEEP_DAILY;
          $result[] = self::_REQUEST_SLEEP_HOURLY;
          break;
        default :
          $result[] = $_request_name;
      }
    }

    return $result;
  }

  /**
   * @inheritdoc
   */
  function saveRequest(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime, $group_id) {
    $this->setPatientUserAPI($patient_api);
    $this->setUserAPI($patient_api->loadTargetObject());
    $this->requests_names = self::verifyRequests($requests_names);
    $this->date_min       = $first_datetime;
    $this->date_max       = $end_datetime;

    // pour les requests autorisées
    foreach ($this->requests_names as $_request_name) {
      $this->clearParams();
      $this->params["request_name"] = CMbArray::get(explode(" ", $_request_name), 0, $_request_name);
      $this->params["request_name_complet"] = $_request_name;
      $this->initialiseConstantParams($this->params["request_name"]);

      do {
        $this->parseDate();
        $this->pushToStack(CMbArray::get($this->params, "date_min"), CMbArray::get($this->params, "date_max"), $group_id);
        $this->params["date_min"] = CMbDT::dateTime("+1 SECOND", CMbArray::get($this->params, "date_max"));
      } while (CMbArray::get($this->params, "more", 0));
    }
  }

  /**
   * @inheritdoc
   */
  public function setUserAPI($user_api) {
    if (!$user_api || !$user_api->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_USER_API);
    }
    $this->user_api = $user_api;
  }

  /**
   * Reset data in params
   *
   * @return void
   */
  public function clearParams() {
    $this->params          = array(
      "date_min" => $this->date_min,
      "date_max" => $this->date_max
    );
    $this->_reload_request = false;
    $this->_acquittement   = null;
    unset($this->_response_code);
  }

  /**
   * Call function to initialise params
   *
   * @param String $request_name constant code
   *
   * @return void
   * @throws CAPITiersException
   */
  public function initialiseConstantParams($request_name) {
    switch ($request_name) {
      case self::REQUEST_WEIGHT:
        $this->initRequestWeight();
        break;

      case "dailyactivity":
      case self::_REQUEST_ACTIVITY_DAILY:
        $this->initRequestDailyActivity();
        break;

      case self::REQUEST_HEARTRATE:
        $this->initRequestHeartRate();
        break;

      case self::REQUEST_HEIGHT:
        $this->initRequestHeight();
        break;

      case "hourlysleep":
      case self::_REQUEST_SLEEP_HOURLY:
        $this->initRequestSleep();
        break;

      case "dailysleep":
      case self::_REQUEST_SLEEP_DAILY:
        $this->initRequestDailySleep();
        break;

      case "hourlyactivity":
      case self::_REQUEST_ACTIVITY_HOURLY:
        $this->initRequestActivity();
        break;

      default:
        throw new CAPITiersException(CAPITiersException::UNSUPPORTED_CONSTANT, $request_name);
    }
  }

  /**
   * Request for weigth of user
   *
   * @return void
   */
  abstract function initRequestWeight();

  /**
   * Request for step of user
   *
   * @return void
   */
  abstract function initRequestDailyActivity();

  /**
   * Request for heartRate of user
   *
   * @return void
   */
  abstract function initRequestHeartRate();

  /**
   * Request for heartRate of user
   *
   * @return void
   */
  abstract function initRequestHeight();

  /**
   * Request for detail activity of user
   *
   * @return void
   */
  abstract function initRequestSleep();

  /**
   * Request for summary activity of user
   *
   * @return void
   */
  abstract function initRequestDailySleep();

  /**
   * Request for detail activity of user
   *
   * @return void
   */
  abstract function initRequestActivity();

  /**
   * Permet de fractionner l'intervalle de date par rapport au max interval
   *
   * @return void
   */
  public function parseDate() {
    $max_interval             = $this->getConstantData(CMbArray::get($this->params, "request_name"), "maxInterval");
    $date_min                 = CMbArray::get($this->params, "date_min");
    $date_max                 = CMbArray::get($this->params, "date_max");
    $this->params["date_max"] = CMbArray::get($this->params, "date_end", $date_max);
    $this->params["more"]     = CMbArray::get($this->params, "more", 0);

    if ($this->checkIntervalDays()) {
      $this->params["more"] = 0;
      unset($this->params["date_end"]);

      return;
    }

    $this->params["date_end"] = CMbArray::get($this->params, "date_end", $date_max);
    $this->params["date_max"] = CMbDT::dateTime("-1 SECOND +$max_interval DAYS", CMbDT::roundTime($date_min, CMbDT::ROUND_DAY));
    $this->params["more"]     = 1;
  }

  /**
   * Check if interval_day <= max_interval_day
   *
   * @return bool
   */
  function checkIntervalDays() {
    $format_date  = CMbArray::get($this->params, "format_date");
    $max_interval = $this->getConstantData(CMbArray::get($this->params, "request_name"), "maxInterval");
    if ($format_date == self::NO_DATE || $max_interval == 0) {
      return true;
    }
    $interval_days = CMbDT::daysRelative(CMbArray::get($this->params, "date_min"), CMbArray::get($this->params, "date_max"));

    return $interval_days <= $max_interval;
  }

  /**
   * Push on the stack the request
   *
   * @param String $datetime_min datetime first
   * @param String $datetime_max datetime end
   * @param int    $group_id     group id of etab
   *
   * @return void
   * @throws CAPITiersException
   */
  public function pushToStack($datetime_min, $datetime_max, $group_id) {
    $request_name         = CMbArray::get($this->params, "request_name");
    $request_name_complet = CMbArray::get($this->params, "request_name_complet");
    $scope                = self::getScopeNeededForRequest($request_name);

    $stack_request                 = new CAPITiersStackRequest();
    $stack_request->api_class      = $this->user_api->_class;
    $stack_request->api_id         = $this->user_api->_id;
    $stack_request->constant_code  = $request_name_complet;
    $stack_request->scope          = $scope;
    $stack_request->datetime_start = $datetime_min;
    $stack_request->datetime_end   = $datetime_max;
    $stack_request->datetime       = CMbDT::dateTime();
    $stack_request->group_id       = $group_id;

    $where = $stack_request->getWhereConditionForMatching();
    if ($stack_request->loadObject($where)) {
      return;
    }
    if (!$stack_request->isOptimizable($this)) {
      $stack_request->optimized = CAPITiersStackRequest::UNOPTIMIZED;
    }
    if ($msg = $stack_request->store()) {
      throw new CAPITiersException(CAPITiersException::INVALID_STORE_STACK_REQUEST, $msg);
    }
  }

  /**
   * Get scope needed for constant code
   *
   * @param String $request_name constant code
   *
   * @return String
   */
  public function getScopeNeededForRequest($request_name) {
    return $this->getConstantData($request_name, "scope");
  }

  /**
   * Load user api and patient user api
   *
   * @param CAPITiersStackRequest $request request
   *
   * @return void
   * @throws CAPITiersException
   */
  function loadUserData($request) {
    /** @var CUserAPI $user_api */
    $user_api = new $request->api_class;
    $user_api->loadObject(array("user_id" => "= '" . $request->api_id . "'"));
    $this->setUserAPI($user_api);

    $patient_user_api = $user_api->loadRefPatientApi();

    $this->setPatientUserAPI($patient_user_api);
  }

  /**
   * @inheritdoc
   */
  public function synchronizeGoals(CPatientUserAPI $patientUserAPI) {
    try {
      $this->setPatientUserAPI($patientUserAPI);
      $this->setUserAPI($patientUserAPI->loadTargetObject());
    }
    catch (CAPITiersException $exception) {
      return;
    }

    $goals = array();
    foreach (CUserAPI::getGoals() as $_obj) {
      try {
        $this->params         = array();
        $this->params["goal"] = $_obj;
        $this->initRequestGoals($_obj);
        $response = $this->SendRequest(
          CMbArray::get($this->params, "url_comp"), CMbArray::get($this->params, "url_params"), CMbArray::get($this->params, "header")
        );
        $goal     = $this->treatGoal($response);
        if (!$goal) {
          continue;
        }
        foreach ($goal as $goal_key => $value) {
          $goals[$_obj][$goal_key] = $value;
        }
      }
      catch (CAPITiersException $exception) {
        try {
          if ($exception->getCode() === CAPITiersException::INVALID_REFRESH_TOKEN && $this->_reload_request) {
            $this->synchronizeGoals($patientUserAPI);

            return;
          }
        }
        catch (CAPITiersException $ex) {
          return;
        }
        continue;
      }
    }

    $update = false;
    foreach ($goals as $_goal => $values) {
      $value = CMbArray::get($values, "value");
      if ($this->user_api->$_goal !== $value) {
        $this->user_api->$_goal = $value;
        $update                 = true;
      }
    }
    if ($update) {
       $this->user_api->store();
    }
  }

  /**
   * Initialize request for get goals
   *
   * @param string $goal goal to get
   *
   * @return void
   * @throws CAPITiersException
   */
  abstract function initRequestGoals($goal);

  /**
   * Send request to API
   *
   * @param string      $url_comp    Add coponements to url
   * @param string      $url_param   Params to set on body request or url params
   * @param string      $header      Authorization header
   * @param string      $method      Method to sent request
   * @param string      $mime_type   Add mime type
   * @param CSourceHTTP $otherSource change source, Base is SourceAPI
   *
   * @return mixed
   */
  abstract function sendRequest(
    $url_comp = null, $url_param = null, $header = null, $method = "GET", $mime_type = null, $otherSource = null
  );

  /**
   * Parse response from API for Goal
   *
   * @param mixed $response_goal response from API
   *
   * @return array
   */
  abstract function treatGoal($response_goal);

  /**
   * @inheritdoc
   */
  public function synchronizeData(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime) {
    $this->setPatientUserAPI($patient_api);
    $user_different = ($this->patient_api->api_user_id != $this->user_api->user_id
      && $this->patient_api->api_user_class != $this->user_api->_class);
    if (!$this->user_api || $user_different) {
      $this->setUserAPI($patient_api->loadTargetObject());
    }
    $this->requests_names = self::verifyRequests($requests_names);
    $this->date_min       = $first_datetime;
    $this->date_max       = $end_datetime;
    $this->check();
    $this->dataRecoveryOn();

    $report = new CActionReport();
    // pour les constants autorisées
    foreach ($this->requests_names as $_request_name) {
      $this->clearParams();
      $this->params["request_name"] = CMbArray::get(explode(" ", $_request_name), 0, $_request_name);
      $this->initialiseConstantParams($_request_name);

      $response = $this->treatRequest();
      if ($this->_reload_request) {
        return $this->synchronizeData($patient_api, $requests_names, $first_datetime, $end_datetime);
      }

      $this->updateMetaData();
      $this->initTreatmentResponse();
      // pour tous les call réalisés
      $sub_report = $this->treatResponse($response);
      $report = $report->fusion($sub_report);
    }
    $this->dataRecoveryOff();

    return $report;
  }

  /**
   * Check all data
   *
   * @return void
   * @throws CAPITiersException
   */
  public function check() {
    foreach ($this->requests_names as $_request_name) {
      $this->checkScope($_request_name);
    }
  }

  /**
   * Check if patient user allow to get this data
   *
   * @param String $request constant
   *
   * @return void
   * @throws CAPITiersException
   */
  protected function checkScope($request) {
    $scope_needed = $this->getScopeNeededForRequest($request);
    if (!CMbArray::in($scope_needed, $this->user_api->getAcceptedScopeToArray())) {
      throw new CAPITiersException(CAPITiersException::INSUFFICIENT_SCOPE);
    }
  }

  /**
   * Use to activate data recovery from api
   *
   * @return void
   */
  protected function dataRecoveryOn() {
    $this->_available_get_data = true;
  }

  /**
   * Treat request to send to API
   *
   * @return array tableau de constantes formaté
   * @throws CAPITiersException
   */
  function treatRequest() {
    $response = array();
    if ($this->checkIntervalDays()) {
      $response[] = $this->executeRequest();

      return $response;
    }
    do {
      $this->parseDate();
      $rep = $this->executeRequest();
      if (count(CMbArray::getRecursive($rep, $this->getAccessBodyRequest())) > 0) {
        $response[] = $rep;
      }
      $this->params["date_min"] = CMbArray::get($this->params, "date_max");
    } while (CMbArray::get($this->params, "more", 0));

    return $response;
  }

  /**
   * Execute request
   *
   * @return mixed
   * @throws CAPITiersException
   */
  abstract function executeRequest();

  /**
   * Get path to access to array
   *
   * @return String
   */
  public function getAccessBodyRequest() {
    return CMbArray::get($this->params, "access_body");
  }

  /**
   * Update meta data
   *
   * @return void
   * @throws CAPITiersException
   */
  public function updateMetaData() {
    if ($this->getAvailabilityDataRecovery()) {
      $date_min = CMbDT::format($this->date_min, CMbDT::ISO_DATE);
      if (!$this->patient_api->synchronized_since || $date_min < $this->patient_api->synchronized_since) {
        $this->patient_api->synchronized_since = $date_min;
      }
      //actualise last_call
      if ($msg = $this->patient_api->store()) {
        throw new CAPITiersException(CAPITiersException::INVALID_STORE_PATIENT_USER_API, $msg);
      }
    }
  }

  /**
   * Know if recovery data is authorized
   *
   * @return bool
   */
  protected function getAvailabilityDataRecovery() {
    return $this->_available_get_data;
  }

  /**
   * Initialize params for treatment response
   *
   * @return void
   * @throws CAPITiersException
   */
  abstract function initTreatmentResponse();

    /**
     * Treat response of api
     *
     * @param array $data data from api
     *
     * @return CActionReport
     * @throws CAPITiersException
     */
    public function treatResponse(?array $data): CActionReport
    {
        $report = new CActionReport();
        foreach ($data as $_response) {
            $response_parsed = $this->parseResponse($_response);
            if (count($response_parsed) > 0) {
                $sub_report = $this->storeConstants($response_parsed);
                $report = $report->fusion($sub_report);
            }
        }

        return $report;
    }

    /**
     * Parse response from API
   *
   * @param array $response response
   *
   * @return mixed
   * @throws CAPITiersException
   */
  abstract function parseResponse($response);

  /**
   * Store each constants from response API
   *
   * @param array $data response from API parsed
   *
   * @return CActionReport
   * @throws CAPITiersException
   */
  public function storeConstants($data) {
    $to_send = array();
    foreach ($data as $_data) {
      $data_constant              = $this->formatToConstant($_data);
      $constant_name              = CMbArray::get($_data, "constant_name");
      if (!$constant_name) {
        continue;
      }
      $data_constant["spec_code"] = $constant_name;
      $to_send[]                  = $data_constant;
    }

    if (!$user_api_id = self::getCronID($this->_class)) {
        throw new CAPITiersException(CAPITiersException::USER_API_ID_NOT_FOUND, $this->_class);
    }

    return CConstantReleve::storeReleveAndConstants($to_send, $this->patient_api->patient_id, $user_api_id);
  }

  /**
   * Get data for constant
   *
   * @param array $data response parsed
   *
   * @return array
   */
  public function formatToConstant($data) {
    $data_constant  = array();
    $data_to_Record = $this->getDataToRecover(CMbArray::get($data, "constant_name"));
    if ($source = CMbArray::get($data, "source")) {
      $data_to_Record["source"] = "";
    }
    if ($validated = CMbArray::get($data, "validated")) {
      $data_to_Record["validated"] = "";
    }
    foreach ($data_to_Record as $_key => $_item) {
      // value => ""
      $key = $_item ? $_item : $_key;
      if (($value = CMbArray::get($data, $_key)) !== null) {
        $data_constant[$key] = $value;
      }
    }

    return $data_constant;
  }

  /**
   * Get array which all data wanted
   *
   * @param String $constant_name constant name
   *
   * @return array
   */
  public function getDataToRecover($constant_name) {

    return CMbArray::get(CMbArray::get($this->params, "data"), $constant_name);
  }

  /**
   * Return id of cron
   *
   * @param String $class_name name of class api
   *
   * @return int
   * @throws CAPITiersException
   */
  public static function getCronID(?string $class_name = null): ?int {
      if (!$class_name) {
          $class_name = static::class;
      }

      $class_name = substr($class_name, strrpos($class_name, '\\') + 1);
      $user_id    = CAppUI::conf("api $class_name user_api_id");
      if ($user_id === "") {
          return null;
      }

    return (int) $user_id;
  }

  /**
   * Use after data recovery finished
   *
   * @return void
   */
  protected function dataRecoveryOff() {
    $this->_available_get_data = false;
  }

  /**
   * Format date in type needed for api
   *
   * @param String $datetime datetime to convert
   * @param String $ref      ref to add
   *
   * @return String date formated
   * @throws CAPITiersException
   */
  public function formatDate($datetime, $ref = null) {
    $format_date = CMbArray::get($this->params, "format_date");
    if ($format_date == self::NO_DATE) {
      return "";
    }

    switch ($format_date) {

      case self::UNIQUE_DATE_ISO:
      case self::DATE_ISO:
        return CMbDT::format($datetime, CMbDT::ISO_DATE);

      case self::UNIQUE_DATETIME_ISO:
      case self::DATETIME_ISO:
        return CMbDT::format($datetime, CMbDT::ISO_DATETIME);

      case self::TIMESTAMPS:
        return CMbDT::toTimestamp($datetime);

      case self::ROUND_DAY_TIMETAMPS:
        return CMbDT::toTimestamp(
          CMbDT::dateTime(
            $ref, CMbDT::roundTime($datetime, CMbDT::ROUND_DAY)
          )
        );
      default:
        throw new CAPITiersException(CAPITiersException::UNSUPPORTED_FORMAT_DATE);
        break;
    }
  }

  /**
   * Know if necessary to update scope and make a call to api for get new token
   *
   * @param CUserAPI $user           User API
   * @param String   $new_permission requests names separate by space
   *
   * @return bool
   */
  public function needToUpdateScope(CUserAPI $user, $new_permission) {
    if ($new_permission == "") {
      return false;
    }
    $scope_needed = $this->getScopeNeededForRequests($new_permission);
    foreach ($scope_needed as $_scope) {
      if (!strstr($user->scope_accepted, $_scope)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get scope needed for constant code
   *
   * @param String $requests requests_names separate by space
   *
   * @return array
   */
  public function getScopeNeededForRequests($requests) {
    $scope_array = array();
    foreach (explode(" ", $requests) as $_request_name) {
      $scope               = $this->getScopeNeededForRequest($_request_name);
      if ($scope) {
        $scope_array[$scope] = $scope;
      }
    }

    return $scope_array;
  }

    /**
     * Load CViewAccessToken of user mediboard who correspond to api
     *
     * @return CViewAccessToken
     * @throws CAPITiersException
     */
    public function loadAccessTokenUserMb()
    {
        if (!$user_api_id = static::getCronID($this->_class)) {
            throw new CAPITiersException(CAPITiersException::USER_API_ID_NOT_FOUND, $this->_class);
        }

        $view_access_token = new CViewAccessToken();
        $ds                = $view_access_token->getDS();
        $where             = [
            "user_id" => $ds->prepare("= ?", $user_api_id),
        ];
        $where[]           = "(datetime_end IS NULL) OR (datetime_end > '" . CMbDT::dateTime() . "')";

        $view_access_token->loadObject($where);

        return $view_access_token;
    }

    /**
   * Treat notification api
   *
   * @param CAPITiersStackRequest $stackRequest request created by function prepareNotification
   *
   * @return void
   * @throws CAPITiersException
   */
  function treatNotification($stackRequest) {
    $where = $stackRequest->getWhereConditionForMatching();
    if (!$stackRequest->loadObject($where)) {
      if (!$stackRequest->isOptimizable($this)) {
        $stackRequest->optimized = CAPITiersStackRequest::UNOPTIMIZED;
      }
      $stackRequest->emetteur = 0; // cas de la notification des Apis
      if ($msg = $stackRequest->store()) {
        throw new CAPITiersException(CAPITiersException::INVALID_STORE_STACK_REQUEST, $msg);
      }
    }
  }

  /**
   * Get constant by categories with data to know if constant is authorized
   *
   * @return array
   */
  public function getConstantsAuthorized() {
    if ($this->user_api) {
      $constants_authorized = $this->user_api->getAcceptedConstantAsArray();
    }
    else {
      $constants_authorized = $this->getAllConstantsAPI();
    }

    $formated_constants = array();
    foreach ($this->getConstantsByCat() as $_cat => $_constants) {
      foreach ($_constants as $_constant_code) {
        $status = in_array($_constant_code, $constants_authorized) ? 1 : 0;
        $formated_constants[$_cat][$_constant_code] = $status;
      }
    }

    return $formated_constants;
  }

  /**
   * Get all constants API
   *
   * @param array $exclude name of constants to exclude
   *
   * @return array
   */
  function getAllConstantsAPI($exclude = array()) {
    $constants = CMbArray::get($this->supported_constants, "all");
    if (count($exclude) > 0) {
      foreach ($exclude as $_constant_name) {
        CMbArray::removeValue($_constant_name, $constants);
      }
    }

    return $constants;
  }

  /**
   * Get constants managed by categories
   *
   * @param String $cat category to get
   *
   * @return array
   */
  function getConstantsByCat($cat = null) {
    if (!$cat) {
      return CMbArray::get($this->supported_constants, "cat");
    }

    return CMbArray::get($this->supported_constants, $cat, array());
  }

  /**
   * Get permisssions
   *
   * @param array $constants specify constants, if null get constants authorized
   *
   * @return array
   */
  public function getPermissions($constants = null) {
    if (!$constants) {
      $constants = $this->user_api->getAcceptedConstantAsArray();
    }

    $permissions = array();
    foreach ($constants as $_constant_code) {
      $permissions[$_constant_code] = $_constant_code;
    }

    return $permissions;
  }

  /**
   * Update subscriptions in function of permissions
   *
   * @param String $new_constants new constants seperate with space
   *
   * @return void
   * @throws CAPITiersException
   */
  public function updateSubscriptions($new_constants) {
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    // si y a eu un probleme, on repart sur une bonne base
    if (!$user_api->subscribe) {
      $this->deleteSubscriptions();
      $this->subscription();

      return;
    }

    $actuel_sub = $this->getSubFromConstants();
    $new_sub    = $this->getSubFromConstants(explode(" ", $new_constants));

    $to_delete = array();
    $to_sub    = array();

    // on parcourt les anciens sub, si y a des subs qui ne sot pas dans les nouveaux on les ajoutes
    foreach ($actuel_sub as $_sub => $_constant_code) {
      if (!CMbArray::get($new_sub, $_sub)) {
        $to_delete[$_sub] = $_constant_code;
      }
    }
    // on parcourt les nouveaux sub, si y a des subs qui n'eatis pas dans l'ancien on les ajoutes
    foreach ($new_sub as $_sub => $_constant_code) {
      if (!CMbArray::get($actuel_sub, $_sub)) {
        $to_sub[$_sub] = $_constant_code;
      }
    }

    if (count($to_delete) > 0) {
      $this->deleteSubscriptions($to_delete);
    }
    if (count($to_sub) > 0) {
      $this->subscription($to_sub);
    }
  }

  /**
   * Delete subcription of user
   *
   * @param array $subscriptions subcription to delete
   *
   * @return mixed
   * @throws CAPITiersException
   */
  abstract function deleteSubscriptions($subscriptions = array());

  /**
   *  Get subscription from authorized constants
   *
   * @param array $constants constants needed / if null, we get authorized constants
   *
   * @return array
   */
  function getSubFromConstants($constants = null) {
    if (!$constants) {
      $constants = $this->user_api->getAcceptedConstantAsArray();
    }

    $constants_sub = $this->getCollection();
    $sub_constants = $this->getCollection(false);
    $response      = array();
    foreach ($constants as $_constant) {
      if ($sub = CMbArray::get($constants_sub, $_constant)) {
        $response[$sub] = CMbArray::get($sub_constants, $sub);
      }
    }

    return $response;
  }

  public function getRequestToTreat($request_name, $authorizations) {
    if ($request_name == self::REQUEST_ACTIVITY) {
      return array(self::_REQUEST_ACTIVITY_DAILY, self::_REQUEST_ACTIVITY_HOURLY);
    }

    if ($request_name == self::REQUEST_SLEEP) {
      return array(self::_REQUEST_SLEEP_DAILY, self::_REQUEST_SLEEP_HOURLY);
    }

    return array($request_name);
  }

  public static function getConstants($request_name) {
    if ($request_name == self::REQUEST_ACTIVITY) {
      return array(self::_REQUEST_ACTIVITY_DAILY, self::_REQUEST_ACTIVITY_HOURLY);
    }

    if ($request_name == self::REQUEST_SLEEP) {
      return array(self::_REQUEST_SLEEP_DAILY, self::_REQUEST_SLEEP_HOURLY);
    }

    return array($request_name);
  }

  /**
   * Get collection infos from two type
   *
   * @param bool $from_constante true : constants => collection ; false collection => constants
   *
   * @return array
   */
  public function getCollection($from_constante = true) {
    if ($from_constante) {
      return CMbArray::get($this->collection_notifications, "from_constant");
    }

    return CMbArray::get($this->collection_notifications, "from_collection");
  }

  /**
   * Get name of constants authorized
   *
   * @return String
   */
  protected function getConstantsAuthorizations() {
    return $this->_constants_authorizations;
  }

  /**
   * Get name of constants authoriations
   *
   * @param String $constants constants name, separate with space
   *
   * @return void
   */
  public function setConstantsAuthorizations($constants) {
    $this->_constants_authorizations = $constants;
  }

  /**
   * Set request id which in progress
   *
   * @param int $request_id id
   *
   * @return void
   */
  public function setRequestID($request_id) {
    $this->request_id = $request_id;
  }
}
