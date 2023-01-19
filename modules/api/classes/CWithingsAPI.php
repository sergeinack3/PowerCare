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
class CWithingsAPI extends CAPITiersOAuth {

  const REQUEST_BODY_COMBO = "bundle";

  static $CONSTANTS_BODY_COMBO = array(self::REQUEST_WEIGHT, self::REQUEST_HEIGHT, self::REQUEST_HEARTRATE, self::REQUEST_ARTERIAL_PRESURE);

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct();

    $this->_limit_application       = true;
    $this->scope                    = "user.metrics,user.activity";
    $this->name_api                 = "withings";
    $this->state                    = array(
      "api_class" => "$this->_class"
    );
    $this->_constant_data           = array(
      self::REQUEST_BODY_COMBO       => array("scope" => "user.metrics", "maxInterval" => 0),
      self::REQUEST_HEIGHT           => array("scope" => "user.metrics", "maxInterval" => 0),
      self::REQUEST_HEARTRATE        => array("scope" => "user.metrics", "maxInterval" => 0),
      self::REQUEST_WEIGHT           => array("scope" => "user.metrics", "maxInterval" => 0),
      self::REQUEST_ARTERIAL_PRESURE => array("scope" => "user.metrics", "maxInterval" => 0),
      self::_REQUEST_SLEEP_HOURLY    => array("scope" => "user.activity", "maxInterval" => 1),
      self::_REQUEST_SLEEP_DAILY     => array("scope" => "user.activity", "maxInterval" => 7),
      self::_REQUEST_ACTIVITY_DAILY  => array("scope" => "user.activity", "maxInterval" => 0),
      self::_REQUEST_ACTIVITY_HOURLY => array("scope" => "user.activity", "maxInterval" => 1),
      self::REQUEST_SLEEP            => array("scope" => "user.activity", "maxInterval" => 0),
      self::REQUEST_ACTIVITY         => array("scope" => "user.activity", "maxInterval" => 0),
    );
    $this->collection_notifications = array(
      "from_constant"   => array(
        self::REQUEST_WEIGHT => "1", self::REQUEST_HEARTRATE => "4", self::REQUEST_ACTIVITY => "16",
        self::REQUEST_SLEEP => "44"
      ),
      "from_collection" => array(
        1 => self::REQUEST_WEIGHT, 4 => self::REQUEST_HEARTRATE, 16 => self::REQUEST_ACTIVITY, 44 => self::REQUEST_SLEEP, 46 => "user"
      )
    );

    $this->supported_constants = array(
      "all" => array(
        self::REQUEST_WEIGHT, self::REQUEST_HEIGHT, self::REQUEST_HEARTRATE, self::REQUEST_ACTIVITY, self::REQUEST_SLEEP, self::REQUEST_ARTERIAL_PRESURE
      ),
      "cat" => array(
        "metrics"  => array(self::REQUEST_WEIGHT, self::REQUEST_HEIGHT, self::REQUEST_HEARTRATE, self::REQUEST_ARTERIAL_PRESURE),
        "activity" => array(self::REQUEST_ACTIVITY, self::REQUEST_SLEEP)
      ));
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
   * @inheritdoc
   */
  public function getUrlAuthentification($scope = null) {
    if (!$scope) {
      $scope = $this->scope;
    }
    $this->loadConf($this->getGroupId());
    $source = self::getSourceAuthentification();
    $query  = $this->generateParamsAuthorization($this->id_client, $scope);

    return "$source->host/oauth2_user/authorize2?" . $query;
  }

  /**
   * Get base url api
   *
   * @param string $method GET or POST method to send
   *
   * @return CSourceHTTP this url
   */
  static function getSourceAuthentification($method = "GET") {
    /** @var CSourceHTTP $source */
    $api             = new self();
    $source          = CExchangeSource::get($api->_class."_authentification_api", CSourceHTTP::TYPE);

    return $source;
  }

  /**
   * @inheritdoc
   */
  public function requestAccessToken() {
    $this->loadConf($this->getGroupId());
    $body =
      array(
        "grant_type"    => "authorization_code",
        "client_id"     => $this->id_client,
        "client_secret" => $this->id_secret,
        "code"          => $this->code,
        "redirect_uri"  => $this->urlCallBack
      );

    $source   = self::getSourceAuthentification();
    $response = $this->sendRequest(
      "oauth2/token?", $body, null, "POST", "application/x-www-form-urlencoded", $source
    );
    if ($this->hasError($response)) {
      $this->treatError($response);
    }

    if ($this->_reload_request) {
      $this->requestAccessToken();
    }

    $response["user_id"] = CMbArray::extract($response, "userid");
    $this->saveData($response);
    $this->setTokenAvaiblable();
  }

  /**
   * @inheritDoc
   */
  public function getCreatedDateAPI() {
    return null;
  }

  /**
   * @inheritdoc
   */
  public function hasError($response) {
    if (CMbArray::get($response, "access_token")) {
      return false;
    }
    $status = intval(CMbArray::get($response, "status", "-1"));
    if ($this->_response_code !== 200 || $status !== 0) {
      return true;
    }

    return false;
  }

  /**
   * @inheritdoc
   */
  public function treatError($error) {
    $status = CMbArray::get($error, "status");
    $status = $status == null ? $this->_response_code: intval($status);
    switch ("".$status) {

      case "100":
      case "101":
      case "102":
        throw new CAPITiersException(CAPITiersException::INVALID_CODE_GRANT_FLOW);
        break;

      case "401":
        if (!$this->token_refreshed) {
          $this->refreshToken();
        }
        else {
          throw new CAPITiersException(CAPITiersException::INVALID_REFRESH_TOKEN);
        }
        break;

      case "214":
      case "277":
      case "2553":
      case "2554":
      case "2555":
        throw new CAPITiersException(CAPITiersException::INSUFFICIENT_SCOPE);

      case "601":
        throw new CAPITiersException(CAPITiersException::TOO_MANY_REQUEST);

      default:
        throw new CAPITiersException(CAPITiersException::UNKNOWN_ERROR, "$status :" . CAppUI::tr("CWithings-msg-unknown code error"));
    }
  }

  /**
   * @inheritdoc
   */
  public function refreshToken($body = null, $header = null, $source = null) {
    $this->loadConf($this->getGroupId());
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    $body     = http_build_query(
      array(
        "grant_type"    => "refresh_token",
        "client_id"     => $this->id_client,
        "client_secret" => $this->id_secret,
        "refresh_token" => $user_api->token_refresh
      )
    );
    $source   = self::getSourceAuthentification();

    parent::refreshToken($body, null, $source);
  }

  /**
   * @inheritdoc
   */
  public function formatAcceptedScope($scope) {
    $explode        = explode(",", $scope);
    $scope_accepted = implode(" ", $explode);

    return $scope_accepted;
  }

  /**
   * @inheritdoc
   */
  function formatScopeToSend($scope) {
    return implode(",", $scope);
  }

  /**
   * @inheritdoc
   */
  function initialiseConstantParams($constant_code) {
    foreach (explode(" ", $constant_code) as $_constant_code) {
      if ($_constant_code == self::REQUEST_BODY_COMBO || CMbArray::in($_constant_code, self::$CONSTANTS_BODY_COMBO)) {
        $this->initRequestGroup();
        return;
      }
    }

    parent::initialiseConstantParams($constant_code);
  }

  /**
   * @inheritDoc
   */
  public function loadConf($group_id) {
    $this->id_client                = CAppUI::gconf("api WithingsAPI api_id", $group_id);
    $this->id_secret                = CAppUI::gconf("api WithingsAPI api_secret", $group_id);
    if ($this->id_client === null || $this->id_client === "" || $this->id_secret === null || $this->id_secret === "") {
      throw new CAPITiersException(CAPITiersException::INVALID_CONF);
    }
  }

  /**
   * @inheritdoc
   */
  function initRequestWeight() {
    $this->setAccessBodyRequest("body measuregrps");
    $this->params["url_comp"]    = "measure";
    $this->params["format_date"] = self::TIMESTAMPS;
    $this->params["wanted_unit"] = array(self::REQUEST_WEIGHT => -3); // pour conversion dynamique de kg en g
    $this->params["url"]         = http_build_query(
      array(
        "action"   => "getmeas",
        "category" => "1", // juste les constantes, pas d'objectif
        "meastype" => "1"  // juste le poids
      )
    );
  }


  /**
   * Manage constant weight, heartrate and height
   *
   * @return void
   */
  function initRequestGroup() {
    $this->setAccessBodyRequest("body measuregrps");
    $this->params["url_comp"]               = "measure";
    $this->params["format_date"]            = self::TIMESTAMPS;
    $this->params["wanted_unit"]            = array(self::REQUEST_WEIGHT => -3, self::REQUEST_HEIGHT => -2); // pour conversion dynamique
    $this->params["url"]                    = http_build_query(
      array(
        "action"   => "getmeas",
        "category" => "1", // juste les constantes, pas d'objectif
      )
    );
  }

  /**
   * @inheritdoc
   */
  function initRequestActivity() {
    $this->setAccessBodyRequest("body series");
    $this->params["url_comp"]    = "v2/measure";
    $this->params["format_date"] = self::TIMESTAMPS;
    $this->params["url"]         = http_build_query(
      array("action" => "getintradayactivity")
    );
  }

  /**
   * @inheritdoc
   */
  function initRequestHeight() {
    $this->setAccessBodyRequest("body measuregrps");
    $this->params["url_comp"]    = "measure";
    $this->params["format_date"] = self::TIMESTAMPS;
    $this->params["wanted_unit"] = array(self::REQUEST_HEIGHT => -2); //conversion dynamique de m en cm
    $this->params["url"]         = http_build_query(
      array(
        "action"   => "getmeas",
        "category" => "1", // juste les constantes, pas d'objectif
        "meastype" => "4"  // juste le height
      )
    );
  }

  /**
   * @inheritdoc
   */
  function initRequestHeartRate() {
    $this->setAccessBodyRequest("body measuregrps");
    $this->params["url_comp"]    = "measure";
    $this->params["format_date"] = self::TIMESTAMPS;
    $this->params["url"]         = http_build_query(
      array(
        "action"   => "getmeas",
        "category" => "1",  // juste les constantes, pas d'objectif
        "meastype" => "11"  // juste le poids
      )
    );
  }

  /**
   * @inheritdoc
   */
  function initRequestDailyActivity() {
    $this->setAccessBodyRequest("body activities");
    $this->params["url_comp"]    = "v2/measure";
    $this->params["format_date"] = self::DATE_ISO;
    $this->params["url"]         = http_build_query(
      array(
        "action"   => "getactivity",
        "category" => "1", // juste les constantes, pas d'objectif
      )
    );
  }

  /**
   * @inheritdoc
   */
  function initRequestSleep() {
    $this->setAccessBodyRequest("body series");
    $this->params["url_comp"]    = "v2/sleep";
    $this->params["format_date"] = self::TIMESTAMPS;
    $this->params["url"]         = http_build_query(
      array(
        "action" => "get"
      )
    );
  }

  /**
   * @inheritdoc
   */
  function initRequestDailySleep() {
    $this->setAccessBodyRequest("body series");
    $this->params["url_comp"]    = "v2/sleep";
    $this->params["format_date"] = self::DATE_ISO;
    $this->params["url"]         = http_build_query(
      array(
        "action" => "getsummary"
      )
    );
  }

  /**
   * @inheritdoc
   */
  function executeRequest() {
    $url      = $this->generateUrl();
    $response = $this->sendRequest(CMbArray::get($this->params, "url_comp"), $url);
    if ($this->hasError($response)) {
      $this->treatError($response);
    }

    return $response;
  }

  /**
   * @inheritdoc
   */
  function generateUrl() {
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    $date_min = self::formatDate(CMbArray::get($this->params, "date_min"));
    $date_max = self::formatDate(CMbArray::get($this->params, "date_max"));
    $url      = CMbArray::get($this->params, "url");
    $url      .= "&userid=" . $user_api->user_api_id;


    $format_date = CMbArray::get($this->params, "format_date");

    // gestion des paramètres de date
    if ($format_date == self::DATE_ISO) {
      $url .= "&startdateymd=" . $date_min . "&enddateymd=" . $date_max;
    }
    else {
      if ($format_date == self::TIMESTAMPS) {
        $url .= "&startdate=" . $date_min / 1000 . "&enddate=" . $date_max / 1000;
      }
    }

    // on ajoute les paramètres spécifique à la requête
    if ($url_params = CMbArray::get($this->params, "url_params")) {
      $url .= "&" . $url_params;
    }

    $url .= "&access_token=" . $user_api->token;

    return $url;
  }

  /**
   * @inheritdoc
   */
  public function initTreatmentResponse() {
    $request_name = CMbArray::get($this->params, "request_name");
    switch ($request_name) {
      case self::_REQUEST_ACTIVITY_HOURLY:
        $this->setDataToRecover(array("steps" => "value", "datetime" => ""), "hourlyactivity");
        $this->setDataToRecover(array("steps" => "value", "datetime" => ""), "dailyactivity");
        $this->setDataToRecover(array("heart_rate" => "value", "datetime" => ""), "heartrate");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "dailydistance");
        $this->setDataToRecover(
          array("min_value" => "", "max_value" => "", "state" => "value", "datetime" => ""), "hourlysleep"
        );
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "dailyheartrate");
        $this->setDataToRecover(array("min_value" => "", "max_value" => "", "datetime" => ""), "heartrateinterval");
        break;
      case self::_REQUEST_ACTIVITY_DAILY:
        $this->setDataToRecover(array("steps" => "value", "datetime" => ""), "dailyactivity");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "dailydistance");
        break;
      case self::_REQUEST_SLEEP_HOURLY:
        $this->setDataToRecover(array("startdate" => "min_value", "enddate" => "max_value", "state" => "value", "datetime" => ""), "hourlysleep");
        break;
      case self::_REQUEST_SLEEP_DAILY:
        $this->setDataToRecover(array("startdate" => "min_value", "enddate" => "max_value", "datetime" => ""), "dailysleep");
        $this->setDataToRecover(array("datetime" => "", "value" => ""), "wakeupduration");
        $this->setDataToRecover(array("datetime" => "", "value" => ""), "lightsleepduration");
        $this->setDataToRecover(array("datetime" => "", "value" => ""), "deepsleepduration");
        $this->setDataToRecover(array("datetime" => "", "value" => ""), "remsleepduration");
        break;
      case self::REQUEST_WEIGHT:
      case self::REQUEST_HEARTRATE:
      case self::REQUEST_HEIGHT:
      case self::REQUEST_ARTERIAL_PRESURE:
      case self::REQUEST_BODY_COMBO:
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "weight");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "height");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "heartrate");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "systole");
        $this->setDataToRecover(array("value" => "", "datetime" => ""), "diastole");
        //$this->setDataToRecover(array("value" => "", "datetime" => ""), "dailysystole"); //todo voir si la constante est nécessaire ?
        //$this->setDataToRecover(array("value" => "", "datetime" => ""), "dailydiastole"); //todo voir si la constante est nécessaire ?
        break;
      default:
        throw new CAPITiersException(CAPITiersException::UNSUPPORTED_CONSTANT);
    }
  }

  /**
   * @inheritdoc
   */
  public function parseResponse($response) {
    $name_request = CMBArray::get($this->params, "request_name");
    switch ($name_request) {

      case self::REQUEST_HEIGHT:
      case self::REQUEST_WEIGHT:
      case self::REQUEST_HEARTRATE:
      case self::REQUEST_ARTERIAL_PRESURE:
      case self::REQUEST_BODY_COMBO:
        return $this->parseBodyMeasures($response);

      case self::_REQUEST_ACTIVITY_HOURLY:
        return $this->parseHourlyActivity($response);

      case self::_REQUEST_ACTIVITY_DAILY:
        return $this->parseDailyActivity($response);

      case self::_REQUEST_SLEEP_HOURLY:
        return $this->parseHourlySleep($response);

      case self::_REQUEST_SLEEP_DAILY:
        return $this->parseDailySleep($response);

      default:
        throw new CAPITiersException(CAPITiersException::UNSUPPORTED_CONSTANT);
    }
  }

  /**
   * Parse response from API for request weight, height, heartrate
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseBodyMeasures($response) {
    $result              = array();
    $correspond_constant = array(1 => "weight", 11 => "heartrate", 4 => "height", 9 => "diastole", 10 => "systole");
    $correspond_request = array("weight" => self::REQUEST_WEIGHT, "heartrate" => self::REQUEST_HEARTRATE, "height" => self::REQUEST_HEIGHT,
                                "diastole" => self::REQUEST_ARTERIAL_PRESURE, "systole" => self::REQUEST_ARTERIAL_PRESURE
    );
    $possible_source     = array(
      0 => CConstantReleve::FROM_DEVICE, 1 => CConstantReleve::FROM_DEVICE, 2 => CConstantReleve::FROM_API,
      4 => CConstantReleve::FROM_API, 7 => CConstantReleve::FROM_DEVICE, 8 => CConstantReleve::FROM_DEVICE
    );
    $array_body          = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    foreach ($array_body as $_response) {
      $datetime = CMbDT::dateTimeFromTimestamp(null, CMbArray::get($_response, "date"));
      $category = CMbArray::get($_response, "category");
      $source   = CMbArray::get($_response, "attrib");
      foreach (CMbArray::get($_response, "measures") as $_data) {
        $type          = CMbArray::get($_data, "type");
        $value         = CMbArray::get($_data, "value");
        $constant_name = CMbArray::get($correspond_constant, $type);
        // si on ne récupére pas la constante ou qu'on a pas l'authorization pour la requête
        if (!$constant_name || !CMbArray::get($correspond_request, $constant_name)) {
          continue;
        }

        $unit = CMbArray::get($_data, "unit");
        $value = $this->convertUnit($value, $unit, CMbArray::getRecursive($this->params, "wanted_unit $constant_name", $unit));
        $data = array(
          "datetime"      => $datetime,
          "category"      => $category,
          "value"         => $value,
          "type"          => $type,
          "constant_name" => $constant_name,
          "source"        => CMbArray::get($possible_source, $source, CConstantReleve::FROM_API)
        );
        if ($source === 0 || $source === 7 || $source === 8) {
          $data["validated"] = 1;
        }
        $result[] = $data;
      }
    }

    return $result;
  }

  private function convertUnit($value, $unit, $wanted_unit) {
    if ($unit == $wanted_unit) {
      return $value;
    }

    $diff = intval($wanted_unit) - intval($unit);
    // conversion du poids en gramme en fonction de l'unit retourné par l'api
    return $diff >= 0 ? intval($value * pow(10, -$diff)) : intval($value. str_repeat("0", -$diff));
  }

  /**
   * Parse response from API for request hourlyactivity
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseHourlyActivity($response) {
    $result             = array();
    $array_body         = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    $steps_day          = 0;
    $date               = null;
    $authorizations      = $this->user_api->getAcceptedConstantAsArray();
    $dailyheartrate     = array();
    $interval_heartrate = array("min" => null, "max" => null);
    foreach ($array_body as $_key => $_response) {
      $steps     = CMbArray::get($_response, "steps");
      $duration  = CMbArray::get($_response, "duration");
      $datetime  = CMbDT::dateTimeFromTimestamp(null, $_key);
      $dt_hourly = CMbDT::roundTime($datetime, CMbDT::ROUND_HOUR);
      if ($steps !== null && $steps > 0 && CMbArray::in(self::REQUEST_ACTIVITY, $authorizations)) {
        $distance = CMbArray::get($_response, "distance");
        $calories = CMbArray::get($_response, "calories");
        if (CMbArray::get($result, $dt_hourly)) {
          $steps_day                      += $steps;
          $result[$dt_hourly]["steps"]    += $steps;
          $result[$dt_hourly]["calories"] += $calories;
          $result[$dt_hourly]["distance"] += $distance;
          $result[$dt_hourly]["duration"] += $duration;
        }
        else {
          $steps_day          += $steps;
          $result[$dt_hourly] = array(
            "steps"         => $steps,
            "datetime"      => $dt_hourly,
            "calories"      => $calories,
            "distance"      => $distance,
            "duration"      => $duration,
            "constant_name" => "hourlyactivity",
            "source"        => CConstantReleve::FROM_DEVICE,
            "validated"     => 1
          );
        }
      }

      $heart_rate = CMbArray::get($_response, "heart_rate");
      if ($heart_rate !== null && $heart_rate > 0 && CMbArray::in(self::REQUEST_HEARTRATE, $authorizations)) {
        $value_heart                         = CMbArray::getRecursive($dailyheartrate, "$dt_hourly value", 0);
        $dailyheartrate[$dt_hourly]["value"] = $value_heart + $heart_rate;
        $dailyheartrate[$dt_hourly]["count"] = CMbArray::getRecursive($dailyheartrate, "$dt_hourly count", 0) + 1;
        $min_heartrate                       = CMbArray::get($interval_heartrate, "min");
        $max_heartrate                       = CMbArray::get($interval_heartrate, "max");
        if (!$min_heartrate || $heart_rate < $min_heartrate) {
          $interval_heartrate["min"] = $heart_rate;
        }

        if (!$max_heartrate || $heart_rate > $max_heartrate) {
          $interval_heartrate["max"] = $heart_rate;
        }
        $result[] = array(
          "datetime"      => $datetime,
          "constant_name" => "heartrate",
          "duration"      => $duration,
          "heart_rate"    => $heart_rate,
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }

      $state = CMbArray::get($_response, "sleep_state");
      if ($state !== null && CMbArray::in(self::REQUEST_SLEEP, $authorizations)) {
        $result[] = array(
          "datetime"      => $datetime,
          "state"         => $state,
          "constant_name" => "hourlysleep",
          "min_value"     => $datetime,
          "max_value"     => CMbDT::dateTime("+$duration SECONDS", $datetime),
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }
    }

    if (CMbArray::in(self::REQUEST_HEARTRATE, $authorizations) && count($dailyheartrate) > 0) {
      $average_heart = 0;
      foreach ($dailyheartrate as $_datetime => $_daily_heartrate) {
        $average_heart += intval(CMbArray::get($_daily_heartrate, "value") / CMbArray::get($_daily_heartrate, "count", 1));
      }
      $average_heart = intval($average_heart / count($dailyheartrate));
      $result[]      = array(
        "datetime"      => CMbArray::get($this->params, "date_min"),
        "value"         => $average_heart,
        "constant_name" => "dailyheartrate",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );
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
    }

    return $result;
  }

  /**
   * Parse response from API for request dailyActivity
   *
   * @param array $response response
   *
   * @return array
   */
  private function parseDailyActivity($response) {
    $result     = array();
    $array_body = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    foreach ($array_body as $_key => $_response) {
      $data["datetime"]      = CMbDT::dateTime(null, CMbArray::extract($_response, "date"));
      $data["source"]        = CConstantReleve::FROM_DEVICE;
      $data["validated"]     = 1;
      if ($steps = CMbArray::get($_response, "steps")) {
        $data["constant_name"] = "dailyactivity";
        $result[] = array_merge($data, array("steps" => $steps));
      }
      if ($distance = CMbArray::get($_response, "distance")) {
        $data["constant_name"] = "dailydistance";
        $result[] = array_merge($data, array("value" => intval($distance)));
      }
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
    $result        = array();
    $array_body    = CMbArray::getRecursive($response, $this->getAccessBodyRequest());
    $convert_state = array(0 => 0, 1 => 1, 2 => 2, 3 => 3);

    foreach ($array_body as $_key => $_response) {
      $startdate = CMbDT::dateTimeFromTimestamp(null, CMbArray::get($_response, "startdate"));
      $result[]  = array(
        "startdate"     => $startdate,
        "enddate"       => CMbDT::dateTimeFromTimestamp(null, CMbArray::get($_response, "enddate")),
        "state"         => CMbArray::get($convert_state, CMbArray::get($_response, "state")),
        "constant_name" => "hourlysleep",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1,
        "datetime"      => $startdate
      );
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
      $date     = CMbDT::format(CMbArray::get($_response, "date"), CMbDT::ISO_DATETIME);
      $result[] = array(
        "model"         => CMbArray::get($_response, "model"),
        "startdate"     => CMbDT::dateTimeFromTimestamp(null, CMbArray::get($_response, "startdate")),
        "enddate"       => CMbDT::dateTimeFromTimestamp(null, CMbArray::get($_response, "enddate")),
        "datetime"      => $date,
        "constant_name" => "dailysleep",
        "source"        => CConstantReleve::FROM_DEVICE,
        "validated"     => 1
      );

      if (($value = CMbArray::getRecursive($_response, "data wakeupduration")) !== null) {
        $result[] = array(
          "value"         => $value,
          "datetime"      => $date,
          "constant_name" => "wakeupduration",
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }

      if (($value = CMbArray::getRecursive($_response, "data lightsleepduration")) !== null) {
        $result[] = array(
          "value"         => $value,
          "datetime"      => $date,
          "constant_name" => "lightsleepduration",
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }

      if (($value = CMbArray::getRecursive($_response, "data deepsleepduration")) !== null) {
        $result[] = array(
          "value"         => $value,
          "datetime"      => $date,
          "constant_name" => "deepsleepduration",
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }

      if (($value = CMbArray::getRecursive($_response, "data remsleepduration")) !== null) {
        $result[] = array(
          "value"         => $value,
          "datetime"      => $date,
          "constant_name" => "remsleepduration",
          "source"        => CConstantReleve::FROM_DEVICE,
          "validated"     => 1
        );
      }
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

    try {
      $this->deleteSubscriptions();
    } catch (CAPITiersException $exception) {
      if ($exception->getCode() !== CAPITiersException::INVALID_DELETE_SUBSCRIPTION) {
        throw $exception;
      }
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
    if (count($subscriptions) == 0) {
      $subscriptions = $this->getSubFromConstants();
    }
    $responses     = $this->_subcriptionSchema("subscribe", $subscriptions);
    $response_code = 0;
    foreach ($responses as $_res) {
      $response_code += CMbArray::get($_res, "response_code", 1);
    }
    if ($response_code) {
      throw new CAPITiersException(CAPITiersException::INVALID_SUBSCRIPTION, $this->_class);
    }
  }

  /**
   * Schema for Call api for subscription
   *
   * @param String $type          type of request list, get, subscribe, revoke
   * @param array  $subscriptions subscriptions in collection notifications
   *
   * @return array
   * @throws CAPITiersException
   */
  private function _subcriptionSchema($type, $subscriptions) {
    /** @var CUserAPIOAuth $user_api */
    $user_api   = $this->user_api;
    $url_params = array(
      "action"       => "$type",
      "access_token" => $user_api->token,
    );
    if ($type == "subscribe" || $type == "revoke") {
      $view_access_token         = $this->loadAccessTokenUserMb();
      $callback                  = CAppUI::conf("appFine address_appFine") . "?token=$view_access_token->hash";
      $url_params["callbackurl"] = $callback;
    }

    $responses = array();
    foreach ($subscriptions as $_sub => $_constant_code) {
      $url_params["appli"] = $_sub;
      $result              = $this->sendRequest("notify", http_build_query($url_params));
      if (($status = CMbArray::get($result, "status", 1)) == 401) {
        try {
          $this->refreshToken();
          $result = $this->sendRequest("notify", http_build_query($url_params));
        }
        catch (CAPITiersException $exception) {
          return array();
        }
      }
      $responses[$_constant_code] = array(
        "response_code" => $status,
        "result"        => $result
      );
    }

    return $responses;
  }

  /**
   * @inheritdoc
   */
  public function deleteSubscriptions($subscriptions = array()) {
    if (count($subscriptions) == 0) {
      $subscriptions = $this->getCollection(false);
    }
    $responses     = $this->_subcriptionSchema("revoke", $subscriptions);
    $response_code = 0;
    foreach ($responses as $_res) {
      $response_code += CMbArray::get($_res, "response_code", 1);
    }
    if ($response_code) {
      throw new CAPITiersException(CAPITiersException::INVALID_DELETE_SUBSCRIPTION, $this->_class);
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
      $res = CMbArray::getRecursive($subs_active, "$_constant result body profiles 0 appli");
      if (!$res) {
        $sub_err[$_sub] = $_constant;
      }
    }

    return $sub_err;
  }

  /**
   * Get subscriptions of user
   *
   * @param array $subscriptions subcription to view
   *
   * @return mixed
   * @throws CAPITiersException
   */
  public function getSubscriptions($subscriptions = array()) {
    return $this->_subcriptionSchema("list", $subscriptions);
  }

  /**
   * @inheritdoc
   */
  public function prepareNotification($params) {
    $appli = CMbArray::get($params, "appli");

    // gestion delete & unlink
    if ($appli == "46") {
      $action = CMbArray::get($params, "action");
      if ($action == "unlink" || $action == "delete") {
        $this->deleteUser();

        return null;
      }
      throw new CAPITiersException(CAPITiersException::INVALID_NOTIFICATION);
    }

    $constant = CMbArray::get($this->getCollection(false), $appli, false);
    if (!$constant) {
      throw new CAPITiersException(CAPITiersException::INVALID_NOTIFICATION);
    }
    $authorizations = $this->user_api->getAcceptedConstantAsArray();
    $requests_names = $this->getRequestToTreat($constant, $authorizations);

    /** @var CAPITiersStackRequest $stackRequest_first */
    $stackRequests = array();
    $dt_start = null;
    foreach ($requests_names as $_key => $_request_name) {
      $stackRequest                = new CAPITiersStackRequest();
      $stackRequest->api_class     = $this->user_api->_class;
      $stackRequest->api_id        = $this->user_api->_id;
      $stackRequest->scope         = $this->getScopeNeededForRequest($constant);
      $stackRequest->constant_code = $_request_name;
      $stackRequest->group_id      = $this->getGroupId();
      if (!$_key) {
        $this->treatNotificationDate($stackRequest, $params);
        $dt_start = CMbDT::roundTime($stackRequest->datetime_start, CMbDT::ROUND_DAY);
      }
      else {
        $stackRequest->datetime_start = $dt_start;
        $stackRequest->datetime_end   = CMbDT::dateTime("+23HOURS +59 MINUTES +59 SECONDS", $dt_start);
      }
      $stackRequests[] = $stackRequest;
    }

    return $stackRequests;
  }

  /**
   * Treat date for notification
   *
   * @param CAPITiersStackRequest $stackRequest stackRequest
   * @param array                 $params       params send by API
   *
   * @return void
   * @throws CAPITiersException
   */
  public function treatNotificationDate(&$stackRequest, $params) {
    $appli = CMbArray::get($params, "appli");
    if ($appli == "16") {
      $date = CMbArray::get($params, "date", false);
      if (!$date) {
        throw new CAPITiersException(CAPITiersException::INVALID_NOTIFICATION);
      }
      $stackRequest->datetime_start = CMbDT::roundTime(CMbDT::format($date, CMbDT::ISO_DATETIME), CMbDT::ROUND_DAY);
      $stackRequest->datetime_end   = CMbDT::dateTime("+1 DAY -1 second", $stackRequest->datetime_start);
    }
    else {
      $startdate = CMbArray::get($params, "startdate", false);
      $enddate   = CMbArray::get($params, "enddate", false);
      if (!$startdate || !$enddate) {
        throw new CAPITiersException(CAPITiersException::INVALID_NOTIFICATION);
      }
      $stackRequest->datetime_start = CMbDT::dateTimeFromTimestamp(null, $startdate);
      $stackRequest->datetime_end   = CMbDT::dateTimeFromTimestamp(null, $enddate);
    }
  }

  /**
   * @inheritdoc
   */
  public function saveRequest(CPatientUserAPI $patient_api, $requests_names, $first_datetime, $end_datetime, $group_id) {
    if (CMbArray::in(self::REQUEST_HEARTRATE, $requests_names) && !CMbArray::in(self::_REQUEST_ACTIVITY_HOURLY, $requests_names)) {
      // récupération du poul de la journée
      $requests_names[] = self::_REQUEST_ACTIVITY_HOURLY;
    }
    $body_request = false;
    foreach ($requests_names as $_request_name) {
      if (CMbArray::in($_request_name, self::$CONSTANTS_BODY_COMBO)) {
        CMbArray::removeValue("$_request_name", $requests_names);
        if (!$body_request) {
          $requests_names[] = self::REQUEST_BODY_COMBO;
          $body_request = true;
        }
      }
    }

    parent::saveRequest($patient_api, $requests_names, $first_datetime, $end_datetime, $group_id);
  }

  /**
   * @inheritdoc
   */
  public function initRequestGoals($goal) {
    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;
    switch ($goal) {
      case "obj_weight":
        $this->setAccessBodyRequest("body measuregrps");
        $this->params["url_comp"]   = "measure";
        $this->params["url_params"] = http_build_query(
          array(
            "action"   => "getmeas",
            "category" => "2", // juste les objectifs
            "meastype" => "1",  // juste le poids
            "userid"   => $user_api->user_api_id
          )
        );
        $this->params["url_params"] = CMbArray::get($this->params, "url_params") . "&access_token=" . $user_api->token;
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

    $body = CMbArray::getRecursive($response_goal, $this->getAccessBodyRequest());
    if (!$body || count($body) === 0) {
      return null;
    }
    $goal = null;
    foreach ($body as $_measure) {
      $timestamps = CMbArray::get($_measure, "date");
      $value      = CMbArray::getRecursive($_measure, "measures 0 value");
      $unit       = CMbArray::getRecursive($_measure, "measures 0 unit");
      if (!$goal) {
        $goal = array(
          "value"    => $value,
          "datetime" => $timestamps,
          "unit" => $unit,
        );
        continue;
      }
      if ($timestamps > CMbArray::get($_measure, "date")) {
        $goal["value"]    = $value;
        $goal["datetime"] = $timestamps;
        $goal["unit"] = $unit;
      }
    }
    if ($date = CMbArray::get($_measure, "datetime")) {
      $goal["datetime"] = CMbDT::dateTimeFromTimestamp(null, $date);
    }
    // unqiuement pour le poids
    $goal["value"] = $this->convertUnit(CMbArray::get($goal, "value"), CMbArray::get($goal, "unit"), -3);

    return $goal;
  }

  public function getRequestToTreat($request_name, $authorizations) {
    $requests_names = parent::getRequestToTreat($request_name, $authorizations);

    if (CMbArray::in(self::REQUEST_ACTIVITY, $authorizations) && $request_name === self::REQUEST_HEARTRATE &&
      !CMbArray::in(self::REQUEST_ACTIVITY, $requests_names)) {
      $requests_names[] = self::_REQUEST_ACTIVITY_HOURLY;
    }

    if (CMbArray::in($request_name, self::$CONSTANTS_BODY_COMBO)) {
      $requests_names[] = self::REQUEST_BODY_COMBO;
      CMbArray::removeValue($request_name, $requests_names);
    }

    return array_values($requests_names);
  }
}
