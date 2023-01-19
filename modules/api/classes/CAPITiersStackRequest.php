<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class CAPITiersStackRequest extends CMbObject {
  const NOT_OPTIMIZED = 0;

  //constants de classe
  const OPTIMIZABLE = 1;
  const UNOPTIMIZED = 2;
  const OPTIMIZED = 3;

  /** @var integer Primary key */
  public $api_tiers_stack_request_id;

  //db field
  public $api_id;
  public $api_class;
  public $constant_code;
  public $scope;
  public $datetime_start;
  public $datetime_end;
  public $receive_datetime;
  public $send_datetime;
  public $agregate;
  public $max_attemp;
  public $nb_request;
  public $time_response;
  public $nb_stored;
  public $optimized;
  public $acquittement;
  public $emptied;
  public $datetime;
  public $group_id;
  public $emetteur;
  // form field
  public $_user_actif = false;
  /** @var CGroups */
  public $_ref_group;
  /** @var CUserAPI */
  public $_ref_user_api;
  /** @var CPatientUserAPI */
  public $_ref_patient_user_api;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "api_tiers_stack_request";
    $spec->key   = "api_tiers_stack_request_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props                     = parent::getProps();
    $props["api_id"]           = "ref meta|api_class notNull back|api_tiers_stack";
    $props["api_class"]        = "enum list|" . implode("|", CPatientUserAPI::getAPiUserClass(CAPITiers::getAPIList())) . " notNull";
    $props["constant_code"]    = "str notNull autocomplete|constant_code";
    $props["scope"]            = "str notNull";
    $props["datetime_start"]   = "dateTime notNull";
    $props["datetime_end"]     = "dateTime notNull";
    $props["receive_datetime"] = "dateTime";
    $props["send_datetime"]    = "dateTime";
    $props["agregate"]         = "num default|0";
    $props["max_attemp"]       = "num default|0";
    $props["nb_request"]       = "num default|1";
    $props["acquittement"]     = "php show|0";
    $props["time_response"]    = "num";
    $props["nb_stored"]        = "num default|0";
    $props["optimized"]        = "num default|0";
    $props["emptied"]          = "bool show|0";
    $props["datetime"]         = "dateTime notNull";
    $props["group_id"]         = "ref class|CGroups autocomplete|text notNull back|api_tiers_stack";
    $props["emetteur"]         = "bool default|1";

    return $props;
  }

  /**
   * Load group associate to this request
   *
   * @return \Ox\Core\CStoredObject|null
   * @throws Exception
   */
  public function loadRefGroup() {
    return $this->_ref_group = $this->loadFwdRef("group_id", true);
  }

  /**
   * Load user api associate to this request
   * @return \Ox\Core\CStoredObject|null
   * @throws Exception
   */
  public function loadRefUserApi() {
    return $this->_ref_user_api = self::loadFromGuid($this->api_class . "-" . $this->api_id, true);
  }

  /**
   * Optimize requests in stack stored
   *
   * @return void
   * @throws CAPITiersException
   * @throws \ReflectionException
   */
  static function optimizeStack() {
    foreach (CAPITiers::getAPIList() as $_api_name) {
      $user_api_id_done = array();
      /** @var CAPITiers $api */
      $api                = new $_api_name;
      $where              = array(
        "acquittement" => "IS NULL",
        "api_class"    => "= '" . CPatientUserAPI::getAPiUserClass($_api_name) . "'"
      );
      $where["optimized"] = "IN ('" . self::NOT_OPTIMIZED . "', '" . self::OPTIMIZABLE . "')";

      $stack         = new CAPITiersStackRequest();
      $stackRequests = $stack->loadList($where);
      /** @var self $_stackRequest */
      foreach ($stackRequests as $_stackRequest) {
        if ($optimized = CMbArray::get($user_api_id_done, $_stackRequest->api_id . "-" . $_stackRequest->constant_code)) {
          // constant_code pour cet utilisateur déjà optimisé
          continue;
        }
        if (!$_stackRequest->isOptimizable()) {
          $user_api_id_done[$_stackRequest->api_id . "-" . $_stackRequest->constant_code] = false;
          $_stackRequest->optimized                                                       = self::UNOPTIMIZED;
          $_stackRequest->store();
          continue;
        }
        $user_api_id_done[$_stackRequest->api_id . "-" . $_stackRequest->constant_code] = true;

        $where_second       = array(
          "api_id"        => "$_stackRequest->api_id",
          "constant_code" => "$_stackRequest->constant_code"
        );
        $stack_to_optimized = self::getRequestIn($stackRequests, $where_second);
        if (count($stack_to_optimized) <= 1) {
          continue;
        }

        $itvl                         = $_stackRequest->determineIntervals($stack_to_optimized, $api->getLimitDayForRequest($_stackRequest->constant_code));
        $min_dt                       = CMbArray::get($itvl, "min");
        $max_dt                       = CMbArray::get($itvl, "max");
        $api->params["constant_name"] = $_stackRequest->constant_code;
        $api->initialiseConstantParams($_stackRequest->constant_code);
        $api->initializeIntervalDate($min_dt, $max_dt);
        //si la limite est depassé
        if (!$api->checkIntervalDays()) {
          $_stackRequest->parseIntervalDays($api);
          continue;
        }

        $_stackRequest->datetime_start = $min_dt;
        $_stackRequest->datetime_end   = $max_dt;
        $_stackRequest->optimized      = self::OPTIMIZABLE;
        $_stackRequest->datetime       = CMbDT::dateTime();
        $_stackRequest->store();
      }
    }
  }

  /**
   * Allow to know if request is optimizable or not
   *
   * @param CAPITiers $api api tool
   *
   * @return bool
   */
  function isOptimizable($api = null) {
    if (!$api) {
      $api_name = CPatientUserAPI::getAPiUserClass($this->api_class);
      /** @var CAPITiers $api */
      $api = new $api_name;
    }

    // 0 ==> pas de restriction et > 1 optimisable
    return $api->getLimitDayForRequest($this->constant_code) != 1;
  }

  /**
   * @inheritdoc
   */
  public function store() {
    if (!$this->datetime) {
      $this->datetime = CMbDT::dateTime();
    }
    /* Possible purge when creating a CAPITiersStackRequest */
    if (!$this->_id) {
      CApp::doProbably(CAppUI::conf('api CAPITiersStackRequest purge_probability'), array($this, 'purgeAllSome'));
    }

    return parent::store();
  }

  /**
   * Get request match with conditions in where in array stack
   *
   * @param CAPITiersStackRequest[] $stack array of stackRequest
   * @param array                   $where conditions "field" => "value"
   *
   * @return array stacks which matched
   */
  static function getRequestIn($stack, $where = array()) {
    $result_requests = array();
    foreach ($stack as $_request) {
      foreach ($where as $_field => $_value) {
        if ($_request->$_field != $_value) {
          continue 2;
        }
      }
      $result_requests[] = $_request;
    }

    return $result_requests;
  }

  /**
   * Dertermine interval between requests
   * and factorise if possible and return date min and date max
   *
   * @param CAPITiersStackRequest[] $stack        stacks requests
   * @param int                     $max_interval interval max for request
   *
   * @return array "min" => dt_min, "max" => dt_max
   * @throws Exception
   */
  function determineIntervals($stack, $max_interval) {
    $min = $this->datetime_start;
    $max = $this->datetime_end;
    foreach ($stack as $_request) {
      if ($_request->_id == $this->_id) {
        continue;
      }
      // Si pas de limite
      if ($max_interval === 0) {
        $min = $_request->datetime_start < $min ? $_request->datetime_start : $min;
        $max = $_request->datetime_end > $max ? $_request->datetime_end : $max;
        $_request->delete();
        continue;
      }

      // dt_end < (min - max_interval)
      if ($_request->datetime_end < CMbDT::dateTime("- $max_interval DAYS", $min)) {
        continue;
      } // dt_start > (max + max_interval)
      elseif ($_request->datetime_start > CMbDT::dateTime("+ $max_interval DAYS", $max)) {
        continue;
      }
      else {
        $min = $_request->datetime_start < $min ? $_request->datetime_start : $min;
        $max = $_request->datetime_end > $max ? $_request->datetime_end : $max;
        $_request->delete();
      }
    }

    return array("min" => $min, "max" => $max);
  }

  /**
   * Parse interval days in several request if interval between two date is too long
   *
   * @param CAPITiers $api api
   *
   * @return void
   * @throws Exception
   */
  private function parseIntervalDays(CAPITiers $api) {
    $max_interval = $api->getLimitDayForRequest($this->constant_code);
    $continued    = true;
    do {
      $date_min = CMbArray::get($api->params, "date_min");
      $date_max = CMbArray::get($api->params, "date_max");

      $interval_days = CMbDT::daysRelative($date_min, $date_max);

      $stack                 = $this;
      $stack->datetime_start = $date_min;
      if ($interval_days > $max_interval) {
        $stack->datetime_end = CMbDT::dateTime("+$max_interval DAYS", $date_min);
        $stack->optimized    = self::OPTIMIZED;
      }
      else {
        $stack->datetime_end = $date_max;
        $stack->optimized    = self::OPTIMIZABLE;
        $continued           = false;
      }
      $stack->store();

      $this->api_tiers_stack_request_id = null;
      $this->_id                        = null;
      $api->params["date_min"]          = $this->datetime_end;
    } while ($continued);
  }

  /**
   * Purge the CAPITiersStackRequest older than the configured threshold
   *
   * @return void
   * @throws \Exception
   */
  public function purgeAllSome() {
    $this->purgeEmptySome();
    $this->purgeDeleteSome();
  }

  /**
   * Purge the CAPITiersStackRequest older than the configured threshold
   *
   * @return void
   * @throws Exception
   */
  function purgeEmptySome() {
    $purge_empty_threshold = CAppUI::conf('api CAPITiersStackRequest purge_empty_threshold');

    $date  = CMbDT::dateTime("- {$purge_empty_threshold} days");
    $limit = intval((CAppUI::conf("api CAPITiersStackRequest purge_probability") * 10) / 2);
    if (!$limit) {
      return null;
    }

    $where             = array();
    $where[]           = "acquittement IS NOT NULL";
    $where["emptied"]  = "= '0'";
    $where["datetime"] = " < '$date'";

    $order = "datetime ASC";

    // Marquage des passages
    $ds              = $this->getDS();
    $requests_ids    = $this->loadIds($where, $order, $limit);
    $in_requests_ids = CSQLDataSource::prepareIn($requests_ids);

    $query = "UPDATE `{$this->_spec->table}` 
              SET `acquittement` = NULL, `emptied` = '1'
              WHERE `{$this->_spec->key}` $in_requests_ids";

    $ds->exec($query);
  }

  /**
   * Purge the CExtractPassages older than the configured threshold
   *
   * @return void
   * @throws Exception
   */
  function purgeDeleteSome() {
    $purge_delete_threshold = CAppUI::conf('api CAPITiersStackRequest purge_delete_threshold');

    $date  = CMbDT::dateTime("- {$purge_delete_threshold} days");
    $limit = intval((CAppUI::conf("api CAPITiersStackRequest purge_probability") * 10) / 2);
    if (!$limit) {
      return null;
    }

    $where             = array();
    $where[]           = "acquittement IS NULL";
    $where["emptied"]  = "= '1'";
    $where["datetime"] = " < '$date'";

    $order = "datetime ASC";

    $request  = new CAPITiersStackRequest();
    $requests = $request->loadList($where, $order, $limit);
    foreach ($requests as $_request) {
      $_request->delete();
    }
  }

  /**
   * Get where condition for matching object when we want push in stack
   *
   * @return array
   */
  function getWhereConditionForMatching() {
    return array(
      "api_class"      => "= '$this->api_class'",
      "api_id"         => "= '$this->api_id'",
      "scope"          => "= '$this->scope'",
      "constant_code"  => "= '$this->constant_code'",
      "acquittement"   => "IS NULL",
      "datetime_start" => "= '$this->datetime_start'",
      "datetime_end"   => "= '$this->datetime_end'"
    );
  }

  /**
   * Treat request
   *
   * @param CAPITiers $api CFitbitAPI | CWithingsAPI
   *
   * @return void
   * @throws CAPITiersException
   */
  function treatRequest($api) {
    try {
      $api->loadUserData($this);
      $api->setRequestID($this->_id);
      $constants = explode("|", $this->constant_code);
      $report    = $api->synchronizeData(
        $api->patient_api, $constants, $this->datetime_start, $this->datetime_end
      );

      $this->nb_stored        = count($report->getConstantsStored());
      $this->send_datetime    = $api->_datetime_send;
      $this->receive_datetime = $api->_datetime_received;
      $this->acquittement     = $api->_acquittement;
      $this->time_response    = $api->_time_response;
      $this->store();
    }
    catch (CAPITiersException $exception) {
      $code = $exception->getCode();
      if ($code === CAPITiersException::INVALID_USER_API) {
        $this->delete();
      }
      elseif ($code === CAPITiersException::INVALID_REFRESH_TOKEN) {
        $this->delete();
        $api->deleteUser();
      }
      else {
        throw new CAPITiersException($code);
      }
    }
  }
}
