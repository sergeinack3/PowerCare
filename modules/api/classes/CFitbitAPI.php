<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Description
 */
class CFitbitAPI extends CAPITiersOAuth {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct();
    $this->scope                    = "activity heartrate profile sleep weight";
    $this->name_api                 = "fitbit";
    $this->state                    = array(
      "api_class" => "$this->_class"
    );
    $this->_constant_data           = array(
      self::REQUEST_WEIGHT           => array("scope" => "weight", "maxInterval" => 31, "collectionType" => "body"),
      self::REQUEST_HEIGHT           => array("scope" => "profile", "maxInterval" => 0),
      self::REQUEST_HEARTRATE        => array("scope" => "heartrate", "maxInterval" => 1, "collectionType" => "activities"),
      self::_REQUEST_SLEEP_HOURLY    => array("scope" => "sleep", "maxInterval" => 1, "collectionType" => "sleep"),
      self::_REQUEST_SLEEP_DAILY     => array("scope" => "sleep", "maxInterval" => 31, "collectionType" => "sleep"),
      self::REQUEST_SLEEP            => array("scope" => "sleep", "maxInterval" => 31, "collectionType" => "sleep"),
      self::_REQUEST_ACTIVITY_DAILY  => array("scope" => "activity", "maxInterval" => 31, "collectionType" => "activities"),
      self::_REQUEST_ACTIVITY_HOURLY => array("scope" => "activity", "maxInterval" => 1, "collectionType" => "activities"),
      self::_REQUEST_DISTANCE        => array("scope" => "activity", "maxInterval" => 31, "collectionType" => "activities"),
      self::REQUEST_ACTIVITY         => array("scope" => "activity", "maxInterval" => 31, "collectionType" => "activities"),
    );
    $this->collection_notifications = array(
      "from_constant"   => array(
        self::REQUEST_WEIGHT => "body", self::REQUEST_ACTIVITY => "activities", self::REQUEST_SLEEP => "sleep"
      ),
      "from_collection" => array(
        "activities" => self::REQUEST_ACTIVITY, "body" => self::REQUEST_WEIGHT, "sleep" => self::REQUEST_SLEEP
      )
    );

    $this->supported_constants = array(
      "all" => array(
        self::REQUEST_WEIGHT, self::REQUEST_HEIGHT, self::REQUEST_HEARTRATE, self::REQUEST_ACTIVITY, self::REQUEST_SLEEP
      ),
      "cat" => array(
        "metrics"  => array(self::REQUEST_WEIGHT, self::REQUEST_HEIGHT, self::REQUEST_HEARTRATE),
        "activity" => array(self::REQUEST_ACTIVITY, self::REQUEST_SLEEP)
      )
    );
  }

  /**
   * Get base url api
   *
   * @param string $method GET or POST method to send
   *
   * @return CSourceHTTP this url
   */
  static function getSourceAPI() {
    /** @var CSourceHTTP $source */
    $api             = new self();
    $source          = CExchangeSource::get($api->_class . "_source_api", CSourceHTTP::TYPE);

    return $source;
  }

  /**
   * @inheritDoc
   */
  public function loadConf($group_id) {
    $this->id_client = CAppUI::gconf("api FitbitAPI api_id", $group_id);
    $this->id_secret = CAppUI::gconf("api FitbitAPI api_secret", $group_id);
    if ($this->id_client === null || $this->id_client === "" || $this->id_secret === null || $this->id_secret === "") {
      throw new CAPITiersException(CAPITiersException::INVALID_CONF);
    }
  }

  /**
   * @inheritdoc
   */
  function getUrlAuthentification($scope = null) {
    $this->loadConf($this->getGroupId());
    if (!$scope) {
      $scope = $this->scope;
    }
    $query = $this->generateParamsAuthorization($this->id_client, $scope);

    return "https://www.fitbit.com/oauth2/authorize?" . $query;
  }

  /**
   * @inheritdoc
   */
  public function requestAccessToken() {
    $this->loadConf($this->getGroupId());
    $body     = array(
      "client_id"    => $this->id_client,
      "grant_type"   => "authorization_code",
      "redirect_uri" => $this->urlCallBack,
      "code"         => $this->code,
      "state"        => $this->getState(),
      "expires_in"   => "3600"
    );
    $response = $this->sendRequest(
      "oauth2/token", $body, $this->generateHeader(self::HEADER_BASIC),
      "POST", "application/x-www-form-urlencoded"
    );
    if ($this->hasError($response)) {
      $this->treatError($response);
    }
    $this->saveData($response);
    $this->token_available = true;
  }

  /**
   * Generate header for request
   *
   * @param int $type header type
   *
   * @return String
   * @throws CAPITiersException
   */
  private function generateHeader($type) {
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    switch ($type) {
      case self::HEADER_BASIC:
        return "Authorization: Basic " . $this->base64Encode();

      case self::HEADER_BEARER:
        return "Authorization: Bearer " . $user_api->token;

      default:
        throw new CAPITiersException(CAPITiersException::INVALID_TYPE_HEADER);
    }
  }

  /**
   * @inheritDoc
   */
  public function getCreatedDateAPI() {
    $url = "1/user/".$this->user_api->user_api_id."/profile.json";
    $response = $this->sendRequest($url, null, $this->generateHeader(self::HEADER_BEARER), "GET");
    if ($this->hasError($response)) {
      $this->treatError($response);

      return $this->getCreatedDateAPI();
    }
    return CMbArray::getRecursive($response, "user memberSince");
  }

  /**
   * Generate base64 code for header request
   *
   * @return String
   * @throws CAPITiersException
   */
  private function base64Encode() {
    $this->loadConf($this->getGroupId());
    return base64_encode($this->id_client . ":" . $this->id_secret);
  }

  /**
   * @inheritdoc
   */
  public function hasError($response) {
    $http_code_response_ok = array(200, 201, 204);
    if (CMbArray::get($response, "errors") || !CMbArray::in($this->_response_code, $http_code_response_ok)) {
      return true;
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  public function treatError($error) {
    if ($err = CMbArray::get($error, "errors")) {
      $err = $err[0];
    }
    else {
      $err = $error;
    }

    if ($this->_response_code === 409) {
      $this->deleteSubscriptions(array(), $err);

      return;
    }

    $typeErr = CMbArray::get($err, "errorType");
    $msg     = CMbArray::get($err, "message");
    switch ($typeErr) {
      case "expired_token":
        $this->refreshToken();
        break;
      case "not_found":
        throw new CAPITiersException(CAPITiersException::API_NOT_FOUND, $msg);
        break;

      case "insufficient_scope":
      case "insufficient_permissions":
        throw new CAPITiersException(CAPITiersException::INSUFFICIENT_SCOPE, $msg);
        break;

      case "invalid_token":
        throw new CAPITiersException(CAPITiersException::INVALID_TOKEN, $msg);
        break;

      case "invalid_request":
      case "request":
        throw new CAPITiersException(CAPITiersException::INVALID_REQUEST, $msg);
        break;

      case "invalid_grant":
        throw new CAPITiersException(CAPITiersException::INVALID_REFRESH_TOKEN, $msg);
        break;

      case "Too Many Requests":
        throw new CAPITiersException(CAPITiersException::TOO_MANY_REQUEST);
        break;

      default:
        throw new CAPITiersException(CAPITiersException::UNKNOWN_ERROR, $msg);
    }
  }

  /**
   * @inheritdoc
   */
  public function deleteSubscriptions($subscriptions = array(), $collection = null) {
    if ($collection) {
      $id_to_delete = CMbArray::get($collection, "subscriptionId");
      $sub_type     = CMbArray::get($collection, "collectionType");

      return $this->_deleteSubscription($sub_type, $id_to_delete);
    }

    if (count($subscriptions) === 0) {
      $subscriptions = $this->getCollection(false);
    }
    // pour chaque subscription que l'api nous renvoie
    $subs = $this->getSubscriptions($subscriptions);
    foreach ($subscriptions as $_sub => $_constant_code) {
      $id_to_delete = CMbArray::getRecursive($subs, "$_sub subscriptionId");
      $sub_type     = CMbArray::getRecursive($subs, "$_sub collectionType");
      if (!$id_to_delete && !$sub_type) {
        continue;
      }
      $this->_deleteSubscription($sub_type, $id_to_delete);
    }

    return true;
  }

  /**
   * Delete subcription of user
   *
   * @param String $sub_type     type collection to delete
   * @param String $id_to_delete id subcriptions to delete
   *
   * @return bool
   * @throws CAPITiersException
   */
  private function _deleteSubscription($sub_type, $id_to_delete) {
    $url_comp = "1/user/" . $this->user_api->user_api_id . "/$sub_type/apiSubscriptions/$id_to_delete.json";
    $response = $this->sendRequest($url_comp, null, $this->generateHeader(self::HEADER_BEARER), "DELETE");

    // reponse 404 quand on delete un user inexitant chez eux
    if ($this->hasError($response) && $this->_response_code !== 404) {
      $this->treatError($response);

      return $this->_deleteSubscription($sub_type, $id_to_delete);
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function getSubscriptions($subscriptions = array()) {
    if (count($subscriptions) === 0) {
      $subscriptions = $this->getSubFromConstants();
    }
    $responses = array();
    foreach ($subscriptions as $_sub => $_constant_code) {
      $url_comp = "1/user/" . $this->user_api->user_api_id . "/$_sub/apiSubscriptions.json";
      $response = $this->sendRequest($url_comp, null, $this->generateHeader(self::HEADER_BEARER), "GET");

      if ($this->hasError($response)) {
        $this->treatError($response);

        return $this->getSubscriptions($subscriptions);
      }
      $responses[$_sub] = CMbArray::getRecursive($response, "apiSubscriptions 0");
    }

    return $responses;
  }

  /**
   * @inheritdoc
   */
  public function refreshToken($body = null, $header = null, $source = null) {
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    $body     = array(
      "grant_type"    => "refresh_token",
      "refresh_token" => $user_api->token_refresh
    );

    parent::refreshToken($body, $this->generateHeader(self::HEADER_BASIC));
  }

  /**
   * @inheritdoc
   */
  public function formatAcceptedScope($scope) {
    return $scope;
  }

  /**
   * @inheritdoc
   */
  function formatScopeToSend($scope) {
    return implode(" ", $scope);
  }

  /**
   * @inheritdoc
   */
  public function initialiseConstantParams($constant_code) {
    switch ($constant_code) {
      case self::_REQUEST_DISTANCE:
        $this->initRequestDistance();
        break;

      default:
        parent::initialiseConstantParams($constant_code);
    }
  }

  /**
   * @inheritdoc
   */
  function initRequestDistance() {
    $this->params["url"]         = "1/user/[user-id]/activities/distance/date/";
    $this->params["format_date"] = self::DATE_ISO;
    $this->setAccessBodyRequest("activities-distance");
  }

  /**
   * @inheritdoc
   */
  function initRequestWeight() {
    $this->params["url"]         = "1/user/[user-id]/body/log/weight/date/";
    $this->params["format_date"] = self::DATE_ISO;
    $this->setAccessBodyRequest("weight");
  }

  /**
   * @inheritdoc
   */
  function initRequestHeight() {
    $this->params["url"]         = "1/user/[user-id]/profile";
    $this->params["format_date"] = self::NO_DATE;
    $this->setAccessBodyRequest("user");
  }

  /**
   * @inheritdoc
   */
  function initRequestHeartRate() {
    $this->params["url"]         = "1/user/[user-id]/activities/heart/date/";
    $this->params["format_date"] = self::UNIQUE_DATE_ISO;
    $this->setAccessBodyRequest("activities-heart");
  }

  /**
   * @inheritdoc
   */
  function initRequestActivity() {
    $this->params["url"]         = "1/user/[user-id]/activities/steps/date/";
    $this->params["format_date"] = self::UNIQUE_DATE_ISO;
    $this->setAccessBodyRequest("activities-steps-intraday dataset");
  }

  /**
   * @inheritdoc
   */
  function initRequestDailyActivity() {
    $this->params["url"]         = "1/user/[user-id]/activities/steps/date/";
    $this->params["format_date"] = self::DATE_ISO;
    $this->setAccessBodyRequest("activities-steps");
  }

  /**
   * @inheritdoc
   */
  function initRequestSleep() {
    $this->params["url"]         = "1.2/user/[user-id]/sleep/date/";
    $this->params["format_date"] = self::DATE_ISO;
    $this->setAccessBodyRequest("sleep");
  }

  /**
   * @inheritdoc
   */
  function initRequestDailySleep() {
    $this->params["url"]         = "1.2/user/[user-id]/sleep/date/";
    $this->params["format_date"] = self::DATE_ISO;
    $this->setAccessBodyRequest("sleep");
  }

  /**
   * @inheritdoc
   */
  public function synchronizeData(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime) {
    if (CMbArray::in(self::_REQUEST_SLEEP_HOURLY, $requests_names)) {
      CMbArray::removeValue(self::_REQUEST_SLEEP_DAILY, $requests_names);
    }
    if (CMbArray::in(self::_REQUEST_ACTIVITY_HOURLY, $requests_names)) {
      CMbArray::removeValue(self::_REQUEST_ACTIVITY_DAILY, $requests_names);
    }

    return parent::synchronizeData($patient_api, $requests_names, $first_datetime, $end_datetime);
  }

  /**
   * @inheritdoc
   */
  public function saveRequest(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime, $group_id) {
    if (CMbArray::in(self::_REQUEST_ACTIVITY_HOURLY, $requests_names)) {
      $requests_names[] = self::_REQUEST_DISTANCE;
    }
    parent::saveRequest($patient_api, $requests_names, $first_datetime, $end_datetime, $group_id);
  }

  /**
   * @inheritdoc
   */
  function executeRequest() {
    $url      = $this->generateUrl();
    $response = $this->sendRequest($url, null, $this->generateHeader(self::HEADER_BEARER));
    if ($this->hasError($response)) {
      $this->treatError($response);
    }

    return $response;
  }

  /**
   * @inheritdoc
   */
  function generateUrl() {
    $date_min = $this->formatDate(CMbArray::get($this->params, "date_min"));
    $date_max = $this->formatDate(CMbArray::get($this->params, "date_max"));
    $url      = CMbArray::get($this->params, "url");
    $url      = str_replace("[user-id]", $this->user_api->user_api_id, $url);

    if (CMbArray::get($this->params, "format_date" == self::NO_DATE)) {
      return $url . ".json";
    }

    if (CMbArray::get($this->params, "format_date") == self::UNIQUE_DATE_ISO) {
      $end_url = CMbArray::get($this->params, "request_name") === self::_REQUEST_ACTIVITY_HOURLY ? "/1d/1min.json" : "/1d/5min.json";

      return $url . $date_min . $end_url;
    }

    return $url . $date_min . "/" . $date_max . ".json";
  }

  /**
   * @inheritdoc
   */
  public function initTreatmentResponse() {
    $request_name = CMbArray::get($this->params, "request_name");
    switch ($request_name) {
      case self::REQUEST_WEIGHT:
        $this->setDataToRecover(array("weight" => "value", "datetime" => ""), "weight");
        break;
      case self::REQUEST_HEIGHT:
        $this->setDataToRecover(array("height" => "value", "datetime" => ""), "height");
        break;
      case self::REQUEST_HEARTRATE:
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "heartrate");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "dailyheartrate");
        $this->setDataToRecover(array("min_value" => "", "max_value" => "", "datetime" => ""), "heartrateinterval");
        break;
      case self::_REQUEST_ACTIVITY_HOURLY:
        $this->setDataToRecover(array("value" => "", "dateTime" => "datetime"), "hourlyactivity");
        $this->setDataToRecover(array("value" => "", "dateTime" => "datetime"), "dailyactivity");
        break;
      case self::_REQUEST_ACTIVITY_DAILY:
        $this->setDataToRecover(array("value" => "", "dateTime" => "datetime"), "dailyactivity");
        break;
      case self::_REQUEST_SLEEP_HOURLY:
        $this->setDataToRecover(
          array("dateTime" => "datetime", "datetime_min" => "min_value", "datetime_max" => "max_value", "level" => "value"), "hourlysleep"
        );
        $this->setDataToRecover(
          array("dateOfSleep" => "datetime", "startTime" => "min_value", "endTime" => "max_value"), "dailysleep"
        );
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "wakeupduration");
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "deepsleepduration");
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "lightsleepduration");
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "remduration");
        break;
      case self::_REQUEST_DISTANCE:
        $this->setDataToRecover(array("dateTime" => "datetime", "value" => ""), "dailydistance");
        break;
      case self::_REQUEST_SLEEP_DAILY:
        $this->setDataToRecover(
          array("dateOfSleep" => "datetime", "startTime" => "min_value", "endTime" => "max_value"), "dailysleep"
        );
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "wakeupduration");
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "deepsleepduration");
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "lightsleepduration");
        $this->setDataToRecover(array("dateOfSleep" => "datetime", "minutes" => "value"), "remduration");
        break;

      default:
        throw new CAPITiersException(CAPITiersException::UNSUPPORTED_CONSTANT);
    }
  }

  /**
   * @inheritdoc
   */
  public function parseResponse($response) {
    $request_name = CMbArray::get($this->params, "request_name");
    switch ($request_name) {
      case self::REQUEST_WEIGHT:
        return $this->parseWeight($response);
      case self::REQUEST_HEIGHT:
        return $this->parseHeight($response);
      case self::REQUEST_HEARTRATE:
        return $this->parseHeartRate($response);
      case self::_REQUEST_ACTIVITY_HOURLY:
        return $this->parseHourlyActivity($response);
      case self::_REQUEST_ACTIVITY_DAILY:
        return $this->parseDailyActivity($response);
      case self::_REQUEST_SLEEP_HOURLY:
        return $this->parseHourlySleep($response);
      case self::_REQUEST_DISTANCE:
        return $this->parseDailyDistance($response);
      case self::_REQUEST_SLEEP_DAILY:
        return $this->parseDailySleep($response);
      default:
        throw new CAPITiersException(CAPITiersException::UNSUPPORTED_CONSTANT);
    }
  }

  /**
   * Parse response from API for request weight
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseWeight($response) {
    $result     = array();
    $array_body = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    foreach ($array_body as $_key => $_response) {
      $_response["datetime"]      = CMbArray::get($_response, "date") . " " . CMbArray::get($_response, "time");
      $_response["constant_name"] = "weight";
      $_response["weight"]        = CMbArray::get($_response, "weight") * 1000; // en gramme
      if ($source = CMbArray::get($_response, "source")) {
        $_response["source"]    = $source === "API" ? CConstantReleve::FROM_API : CConstantReleve::FROM_DEVICE;
        $_response["validated"] = $source === "API" ? 0 : 1;
      }
      $result[] = $_response;
    }

    return $result;
  }

  /**
   * Parse response from API for request height
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseHeight($response) {
    $result                      = array();
    $UNIT_INCHES                 = 2.54;
    $array_body                  = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    $array_body["constant_name"] = "height";
    $array_body["datetime"]      = CMbDT::dateTime();
    $array_body["source"]        = CConstantReleve::FROM_API;
    if (CMbArray::get($response, "heightUnit") === "US") {
      $array_body["height"] = CMbArray::get($response, "height") * $UNIT_INCHES;
    }
    $result[] = $array_body;

    return $result;
  }

  /**
   * Parse response from API for request heartrate
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseHeartRate($response) {
    $result = array();

    $array_body = CMbArray::getRecursive($response, $this->getAccessBodyRequest(), array());
    foreach ($array_body as $_key => $_response) {
      $data = array();
      if (($avg_heart = CMbArray::getRecursive($_response, "value restingHeartRate")) === null) {
        continue;
      }
      $datetime              = CMbDT::dateTime(null, CMbArray::get($_response, "dateTime"));
      $data["constant_name"] = "dailyheartrate";
      $data["datetime"]      = $datetime;
      $data["value"]         = $avg_heart;
      $data["source"]        = CConstantReleve::FROM_DEVICE;
      $data["validated"]     = 1;
      $result[]              = $data;
    }
    $interval_heartrate = array();
    $date               = CMbDT::roundTime(CMbArray::get($this->params, "date_min"), CMbDT::ROUND_DAY);
    foreach (CMbArray::getRecursive($response, "activities-heart-intraday dataset", array()) as $_key => $_response) {
      $data = array();
      if (($heartrate = CMbArray::get($_response, "value")) === null || ($time = CMbArray::get($_response, "time")) === null) {
        continue;
      }
      $min_heartrate = CMbArray::get($interval_heartrate, "min");
      $max_heartrate = CMbArray::get($interval_heartrate, "max");
      if (!$min_heartrate || $heartrate < $min_heartrate) {
        $interval_heartrate["min"] = $heartrate;
      }

      if (!$max_heartrate || $heartrate > $max_heartrate) {
        $interval_heartrate["max"] = $heartrate;
      }
      $datetime              = str_replace("00:00:00", $time, $date);
      $data["constant_name"] = "heartrate";
      $data["datetime"]      = $datetime;
      $data["value"]         = $heartrate;
      $data["source"]        = CConstantReleve::FROM_DEVICE;
      $data["validated"]     = 1;
      $result[]              = $data;
    }

    $min_heartrate = CMbArray::get($interval_heartrate, "min");
    $max_heartrate = CMbArray::get($interval_heartrate, "max");
    if ($min_heartrate && $max_heartrate) {
      $result[] = array(
        "datetime"      => CMbArray::get($this->params, "date_min"),
        "min_value"     => $min_heartrate,
        "max_value"     => $max_heartrate,
        "constant_name" => "heartrateinterval",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );
    }

    return $result;
  }

  /**
   * Parse response from API for request hourlyactivity
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseHourlyActivity($response) {
    $result     = array();
    $array_body = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    /** @var CUserAPIOAuth $user_api */
    $user_api            = $this->user_api;
    $constant_authorized = $user_api->getAcceptedConstantAsArray();
    $datetime            = CMbArray::getRecursive($response, "activities-steps 0 dateTime");
    $value_day           = CMbArray::getRecursive($response, "activities-steps 0 value");
    if (CMbArray::in(self::REQUEST_ACTIVITY, $constant_authorized)) {
      $result[] = array(
        "dateTime" => CMbDT::format($datetime, CMbDT::ISO_DATETIME), "value" => $value_day, "constant_name" => "dailyactivity"
      );
    }
    foreach ($array_body as $_key => $_response) {
      $time  = CMbArray::get($_response, "time");
      $value = CMbArray::get($_response, "value");
      if ($value === 0) {
        continue;
      }
      $datetime_hourly = CMbDT::roundTime($datetime . " " . $time, CMbDT::ROUND_HOUR);
      if (CMbArray::get($result, $datetime_hourly)) {
        $result[$datetime_hourly]["value"] += $value;
      }
      else {
        $result[$datetime_hourly] = array(
          "dateTime"      => $datetime_hourly,
          "value"         => $value,
          "constant_name" => "hourlyactivity",
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }
    }

    return $result;
  }

  /**
   * Parse response from API for request dailyactivity
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseDailyActivity($response) {
    $result     = array();
    $array_body = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    foreach ($array_body as $_key => $_response) {
      if (CMbArray::get($_response, "value") == 0) {
        continue;
      }
      $_response["constant_name"] = "dailyactivity";
      $_response["source"]        = CConstantReleve::FROM_DEVICE;
      $_response["dateTime"]      = CMbDT::format(CMbArray::get($_response, "dateTime"), CMbDT::ISO_DATETIME);
      $_response["validated"]     = 1;
      $_response["dateTime"]      = CMbArray::extract($_response, "dateTime");
      $result[]                   = $_response;
    }

    return $result;
  }

  /**
   * Parse response from API for request hourlysleep
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseHourlySleep($response) {
    $result               = array();
    $array_body           = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    $state_correspondence = array("wake" => 0, "light" => 1, "deep" => 2, "rem" => 3);
    foreach ($array_body as $_key => $_response) {
      foreach (CMbArray::getRecursive($_response, "levels data") as $_data) {
        $seconds  = CMbArray::get($_data, "seconds");
        $date_min = CMbDT::format(CMbArray::get($_data, "dateTime"), CMbDT::ISO_DATETIME);
        $date_max = CMbDT::dateTime("+ $seconds SECONDS", $date_min);
        $level    = CMbArray::get($state_correspondence, CMbArray::get($_data, "level"));
        $result[] = array(
          "dateTime"      => $date_min,
          "datetime_min"  => $date_min,
          "datetime_max"  => $date_max,
          "seconds"       => $seconds,
          "level"         => $level,
          "constant_name" => "hourlysleep",
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }
      $this->parseSummary($_response, $result);
    }

    return $result;
  }

  /**
   * Parse response from API for constant remduration, deepduration, lightduration, wakeduration, dailysleep
   *
   * @param array $response response
   * @param array $result   array to push formated constant
   *
   * @return void
   */
  private function parseSummary($response, &$result) {
    $starttime = CMbDT::format(CMbArray::get($response, "startTime"), CMbDT::ISO_DATETIME);
    $endtime   = CMbDT::format(CMbArray::get($response, "endTime"), CMbDT::ISO_DATETIME);
    $datetime  = CMbDT::format(CMbArray::get($response, "dateOfSleep"), CMbDT::ISO_DATETIME);

    $result[] = array(
      "startTime"     => $starttime,
      "endTime"       => $endtime,
      "dateOfSleep"   => $datetime,
      "constant_name" => "dailysleep",
      "source"        => CConstantReleve::FROM_DEVICE,
      "validated"     => 1
    );

    if (($value = CMbArray::getRecursive($response, "levels summary deep minutes")) !== null) {
      $result[] = array(
        "minutes"       => $value * 60,
        "dateOfSleep"   => $datetime,
        "constant_name" => "deepsleepduration",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );
    }

    if (($value = CMbArray::getRecursive($response, "levels summary light minutes")) !== null) {
      $result[] = array(
        "minutes"       => $value * 60,
        "dateOfSleep"   => $datetime,
        "constant_name" => "lightsleepduration",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );
    }

    if (($value = CMbArray::getRecursive($response, "levels summary wake minutes")) !== null) {
      $result[] = array(
        "minutes"       => $value * 60,
        "dateOfSleep"   => $datetime,
        "constant_name" => "wakeupduration",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );
    }

    if (($value = CMbArray::getRecursive($response, "levels summary rem minutes")) !== null) {
      $result[] = array(
        "minutes"       => $value * 60,
        "dateOfSleep"   => $datetime,
        "constant_name" => "remduration",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );
    }
  }

  /**
   * Parse response from API for request weight
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseDailyDistance($response) {
    $result = array();

    foreach (CMbArray::get($response, $this->getAccessBodyRequest()) as $_data) {
      if (CMbArray::get($_data, "value") > 0) {
        $_data["value"]         = intval(CMbArray::get($_data, "value") * 1000);
        $_data["dateTime"]      = CMbDT::format(CMbArray::get($_data, "dateTime"), CMbDT::ISO_DATETIME);
        $_data["constant_spec"] = "dailydistance";
        $_data["source"]        = CConstantReleve::FROM_DEVICE;
        $_data["validated"]     = 1;
        $result[]               = $_data;
      }
    }

    return $result;
  }

  /**
   * Parse response from API for request dailysleep
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseDailySleep($response) {
    $result     = array();
    $array_body = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    foreach ($array_body as $_key => $_response) {
      $this->parseSummary($_response, $result);
    }

    return $result;
  }

  /**
   * @inheritdoc
   */
  public function requestRevokeAccess() {
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    if (!$user_api || !$user_api->_id) {
      throw new CAPITiersException(CAPITiersException::INVALID_USER_API);
    }

    $this->deleteSubscriptions();

    $body = array(
      "token" => $user_api->token
    );

    $response = $this->sendRequest(
      "oauth2/revoke", $body, $this->generateHeader(self::HEADER_BASIC),
      "POST", "application/x-www-form-urlencoded"
    );

    if ($this->hasError($response)) {
      $this->treatError($response);

      return $this->requestRevokeAccess();
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  public function requestRevokeAvailable() {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function hasSubscription() {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function subscription($subscriptions = array()) {
    if (count($subscriptions) === 0) {
      $subscriptions = $this->getSubFromConstants();
    }
    foreach ($subscriptions as $_sub => $_constant_code) {
      // ici, on défini l'id unique (user_api->_id) pour la liaison collections, user api
      $url_comp = "1/user/" . $this->user_api->user_api_id . "/$_sub/apiSubscriptions/" . $this->user_api->_id . "-$_sub.json";
      $response = $this->sendRequest($url_comp, null, $this->generateHeader(self::HEADER_BEARER), "POST");

      if ($this->hasError($response)) {
        $this->treatError($response);

        $this->subscription();

        return;
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function verifySubscription() {
    $sub_wanted  = $this->getSubFromConstants();
    $subs_active = $this->getSubscriptions($sub_wanted);
    $sub_err     = array();

    foreach ($sub_wanted as $_sub => $_constant) {
      $res = CMbArray::get($subs_active, "$_sub");
      if (!$res) {
        $sub_err[$_sub] = $_constant;
      }
    }

    return $sub_err;
  }

  /**
   * @inheritDoc
   */
  public function getRequestToTreat($request_name, $authorizations) {
    $requests = parent::getRequestToTreat($request_name, $authorizations);
    if ($request_name == self::REQUEST_ACTIVITY) {
      $requests[] = self::_REQUEST_DISTANCE;
      $requests[] = self::REQUEST_HEARTRATE;
    }

    return $requests;
  }

  /**
   * @inheritdoc
   */
  function prepareNotification($params) {
    $collection_type = CMbArray::get($params, "collectionType");
    $date            = CMbArray::get($params, "date");
    if ($collection_type == "deleteUser") {
      $this->deleteUser();
      return array();
    }

    $constant_code = CMbArray::get($this->getCollection(false), "$collection_type");
    $requests      = $this->getRequestToTreat($constant_code, array());

    $stackrequests = array();
    foreach ($requests as $_request) {
      $stackrequest                 = new CAPITiersStackRequest();
      $stackrequest->api_id         = $this->user_api->_id;
      $stackrequest->api_class      = $this->user_api->_class;
      $stackrequest->scope          = $this->getScopeNeededForRequest($_request);
      $stackrequest->constant_code  = $_request;
      $stackrequest->group_id       = $this->getGroupId();
      $stackrequest->datetime_start = CMbDT::format($date, CMbDT::ISO_DATETIME);
      $stackrequest->datetime_end   = CMbDT::dateTime("+1 DAY -1 SECOND", $stackrequest->datetime_start);

      $stackrequests[] = $stackrequest;
    }

    return $stackrequests;
  }

  /**
   * @inheritdoc
   */
  public function initRequestGoals($goal) {
    switch ($goal) {
      case "obj_weight":
        $this->params["url_comp"] = "1/user/" . $this->user_api->user_api_id . "/body/log/weight/goal.json";
        $this->params["header"]   = $this->generateHeader(self::HEADER_BEARER);
        $this->params["key_data"] = "weight";
        $this->setAccessBodyRequest("goal");
        break;

      case "obj_steps":
        $this->params["url_comp"] = "1/user/" . $this->user_api->user_api_id . "/activities/goals/daily.json";
        $this->params["header"]   = $this->generateHeader(self::HEADER_BEARER);
        $this->params["key_data"] = "steps";
        $this->setAccessBodyRequest("goals");
        break;
      default:
        throw new CAPITiersException(CAPITiersException::INVALID_GOALS);
    }
  }

  /**
   * @inheritdoc
   * @throws CAPITiersException
   */
  public function treatGoal($response_goal) {
    if ($this->hasError($response_goal)) {
      $this->treatError($response_goal);
      if ($this->_reload_request) {
        throw new CAPITiersException(CAPITiersException::INVALID_REFRESH_TOKEN);
      }
    }
    $goal  = CMbArray::get($this->params, "goal");
    $body  = CMbArray::getRecursive($response_goal, $this->getAccessBodyRequest());
    $value = CMbArray::get($body, CMbArray::get($this->params, "key_data"));
    if ($goal === "obj_weight") {
      $value *= 1000; // set unit to gramme
    }

    return array("value" => $value);
  }
}
