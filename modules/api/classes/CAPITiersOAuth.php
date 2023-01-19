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
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

/**
 * Description
 */
abstract class CAPITiersOAuth extends CAPITiers {
  const HEADER_BASIC = 0;
  const HEADER_BEARER = 1;
  protected $state = array();
  protected $code;
  protected $token_available = false;
  protected $token_refreshed = false;

  /**
   * @inheritdoc
   */
  public function authorizeApp() {
    $this->requestAuthorization();
    $this->requestAccessToken();
  }

  /**
   * Request authorization for api, Attention, checkin() à faire après
   *
   * @return void
   * @throws CAPITiersException
   */
  public function requestAuthorization() {
    $code_grant_flow = CView::get("code", "str");
    if (!$code_grant_flow) {
      throw new CAPITiersException(CAPITiersException::INVALID_CODE_GRANT_FLOW);
    }
    $this->code = $code_grant_flow;

    $authorizations_constants = CView::get("authorizations", "str");
    if ($authorizations_constants) {
      $this->setConstantsAuthorizations(urldecode($authorizations_constants));
    }
  }

  /**
   * Request to get access token
   *
   * @return void
   * @throws CAPITiersException
   */
  public abstract function requestAccessToken();

  /**
   * Generate html query to get authorization code grant flow.
   *
   * @param string $id_client client id client application
   * @param String $scope     scope
   *
   * @return string query html whit params.
   */
  public function generateParamsAuthorization($id_client, $scope) {
    // add permissions constants
    if ($authorizations = $this->getConstantsAuthorizations()) {
      $this->addState("authorizations", $authorizations);
    }

    $params = array(
      "response_type" => "code",
      "client_id"     => $id_client,
      "scope"         => $scope,
      "state"         => $this->getState(),
      "redirect_uri"  => $this->urlCallBack
    );

    $query = http_build_query($params);

    return $query;
  }

  /**
   * Add a param state, this param is return by API
   *
   * @param String $key   name of key
   * @param String $value value
   *
   * @return void
   */
  public function addState($key, $value) {
    $this->state[$key] = $value;
  }

  /**
   * Get and encode state in url
   *
   * @return String|null
   */
  public function getState() {
    $formted_state = "";
    if (!$api_class = CMbArray::extract($this->state, "api_class")) {
      return null;
    }
    $formted_state .= "api_class=$api_class";
    foreach ($this->state as $_key => $_value) {
      $formted_state .= "&$_key=$_value";
    }

    return urlencode($formted_state);
  }

  /**
   * @inheritDoc
   */
  public function clearParams() {
    $this->token_refreshed = false;
    parent::clearParams();
  }

  /**
   * @inheritdoc
   */
  public function aedUserAPI($patient_id) {
    if (!$this->isAvailableToken()) {
      throw new CAPITiersException(CAPITiersException::TOKEN_NOT_AVAILABLE);
    }

    $user_id          = CMbArray::get($this->params, "user_id");
    $token            = CMbArray::get($this->params, "access_token");
    $token_refresh    = CMbArray::get($this->params, "refresh_token");
    $token_duration   = CMbArray::get($this->params, "expires_in");
    $scope_accepted   = CMbArray::get($this->params, "scope_accepted");
    $user_api_name    = $this->getUserAPIName();
    $request_accepted = $this->getConstantsAuthorizations();
    // changement de scope
    if ($this->patient_api) {
      /** @var CUserAPIOAuth $user_api */
      $user_api = $this->patient_api->loadTargetObject();
      if (!$this->user_api) {
        $this->setUserAPI($user_api);
      }

      // si conflit entre id des comptes synchro
      if ($user_id !== $user_api->user_api_id) {
        $this->setAccountConflict($user_id);
      }
    }
    // création du compte
    else {
      /** @var CUserAPI $user_api */
      $user_api              = new $user_api_name;
      $user_api->user_api_id = $user_id;
      $user_api->active      = 0;
      $user_api->loadMatchingObject();
    }
    $user_api->token             = $token;
    $user_api->token_refresh     = $token_refresh;
    $user_api->scope_accepted    = $scope_accepted;
    $user_api->constant_accepted = $request_accepted;
    $user_api->active            = 1;
    $user_api->subscribe         = 0;

    $timestamp                 = CMbDT::toTimestamp(CMbDT::dateTime()) / 1000;
    $user_api->expiration_date = CMbDT::dateTimeFromTimestamp(null, $timestamp + $token_duration);
    if ($msg = $user_api->store()) {
      throw new CAPITiersException(CAPITiersException::INVALID_STORE_USER_API, $msg);
    }
    $patient = new CPatient();
    $patient->load($patient_id);
    if (!$patient->_id || !CAppUI::gconf("appFine APITiers subscribes_api_{$this->name_api}_active", $patient->loadRefFirstPatientUser()->group_id)) {
      return $user_api;
    }

    if ($this->hasSubscription() && $this->patient_api) {
      $this->updateSubscriptions($request_accepted);
    }

    return $user_api;
  }

  /**
   * Know if token is available
   *
   * @return boolean
   */
  protected function isAvailableToken() {
    return $this->token_available;
  }

  /**
   * @inheritdoc
   */
  public function check() {
    $this->checkValideToken();

    parent::check();
  }

  /**
   * Check if token is always available
   *
   * @return void
   * @throws CAPITiersException
   */
  public function checkValideToken() {
    /** @var CUserAPIOAuth $user_api */
    $user_api        = $this->user_api;
    $date            = date(CMbDT::dateTime());
    $date_expiration = date($user_api->expiration_date);
    if ($date > $date_expiration) {
      $this->refreshToken($user_api);
    }
  }

  /**
   * Request refresh token access
   *
   * @param null $body   body from request
   * @param null $header header from request
   * @param null $source source from request
   *
   * @return void
   * @throws CAPITiersException
   */
  public function refreshToken($body = null, $header = null, $source = null) {
    $response = $this->sendRequest(
      "oauth2/token", $body, $header,
      "POST", "application/x-www-form-urlencoded", $source
    );

    $this->token_refreshed = true;
    if ($this->hasError($response)) {
      $this->treatError($response);
    }

    $this->_reload_request = true;

    /** @var CUserAPIOAuth $user_api */
    $user_api = $this->user_api;

    // enregistrement des nouveaux tokens
    $user_api->token           = CMbArray::get($response, "access_token");
    $user_api->token           = CMbArray::get($response, "access_token");
    $user_api->token_refresh   = CMbArray::get($response, "refresh_token");
    $token_duration            = CMbArray::get($response, "expires_in");
    $date                      = CMbDT::toTimestamp(CMbDT::dateTime()) / 1000;
    $user_api->expiration_date = CMbDT::format(date('Y/m/d H:i:s', $date + $token_duration), CMbDT::ISO_DATETIME);
    if ($msg = $user_api->store()) {
      throw new CAPITiersException(CAPITiersException::INVALID_STORE_USER_API, $msg);
    }
  }

  /**
   * @inheritdoc
   */
  function sendRequest($url_comp = null, $url_param = null, $header = null, $method = "GET", $mime_type = null, $otherSource = null) {
    /** @var CFitbitAPI|CWithingsAPI $this */
    $source = $this->getSourceAPI();

    if (!$source || !$source->_id) {
      CAppUI::stepAjax("CSourceHTTP.none", UI_MSG_ERROR);
    }

    if ($otherSource) {
      $source = $otherSource;
    }

    $this->_datetime_send = CMbDT::dateTime();
    $start                = microtime(true) * 1000;

    $client = $source->getClient();
    $options = [];
    if ($method === 'GET' && $url_param) {
        $options['query'] = $url_param;
    } elseif ($url_param) {
        $options['body'] = $url_param;
    }

    if ($mime_type) {
        $options['headers']['Content-Type'] = $mime_type;
    }

    if ($header) {
        [$header_name, $header_value] = explode(': ', $header, 2);
        $options['headers'][$header_name] = $header_value;
    }

    $response = $client->request($method, "$source->host/$url_comp", $options);

    $this->_datetime_received = CMbDT::dateTime();

    $this->_acquittement  = $acq = $response->getBody()->__toString();
    $end                  = microtime(true) * 1000;
    $this->_time_response = intval($end - $start);
    $this->_response_code = $response->getStatusCode();

    return json_decode($acq, true);
  }

  /**
   * Save data on session.
   *
   * @param array $params parameters to save
   *
   * @return void
   */
  public function saveData($params) {
    $scope = CMbArray::get($params, "scope");

    $this->params["access_token"]   = CMbArray::get($params, "access_token");
    $this->params["expires_in"]     = CMbArray::get($params, "expires_in");
    $this->params["refresh_token"]  = CMbArray::get($params, "refresh_token");
    $this->params["token_type"]     = CMbArray::get($params, "token_type");
    $this->params["scope_accepted"] = $this->formatAcceptedScope($scope);
    $this->params["user_id"]        = CMbArray::get($params, "user_id");
  }

  /**
   * Generate Url data of APIs
   *
   * @return string formated Url
   * @throws CAPITiersException
   */
  public abstract function generateUrl();

  /**
   * Use when token is available
   *
   * @return void
   */
  protected function setTokenAvaiblable() {
    $this->token_available = true;
  }
}
