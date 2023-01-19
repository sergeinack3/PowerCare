<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CExchangeHTTP;

/**
 * Description
 */
abstract class CSync implements IShortNameAutoloadable {
  static $api_version = 1;
  static $prettify = false;

  /** @var CExchangeHTTP */
  static $http_exchange;

  /** @var Chronometer */
  static $http_exchange_chrono;

  static $response_codes = [
    '200' => ['OK', 'ok'],
    '201' => ['Created', 'ok'],
    '203' => ['Wrong', 'error'],
    '204' => ['Modified', 'ok'],
    '205' => ['Deleted', 'ok'],
    '208' => ['Already Reported', 'ok'],
    '210' => ['Duplicate data', 'error'],
    '403' => ['Forbidden', 'error'],
    '404' => ['Not Found', 'error'],
    '410' => ['No permission', 'error'],
    '412' => ['Precondition Failed', 'error'],
    '500' => ['Internal Server Error', 'error'],
    '501' => ['Not Implemented', 'error'],
    '505' => ['HTTP Version not supported', 'error'],
  ];

  /**
   * Get a response message according to given code
   *
   * @param string $code Response code
   *
   * @return mixed|null
   */
  static function getResponseMessage($code) {
    if (!isset(static::$response_codes[$code])) {
      return null;
    }

    return reset(static::$response_codes[$code]);
  }

  /**
   * Get a response status according to given code
   *
   * @param string $code Response code
   *
   * @return mixed|null
   */
  static function getResponseStatus($code) {
    if (!isset(static::$response_codes[$code])) {
      return null;
    }

    return end(static::$response_codes[$code]);
  }

  /**
   * Initialise an API call
   *
   * @param string  $api_name    API method name
   * @param string  $api_version Version of the API
   * @param boolean $prettify    JSON_PRETTIFY
   *
   * @return void
   * @throws \Exception
   */
  static function init($api_name, $api_version, $prettify) {
    static::$api_version = ($api_version) ?: static::$api_version;
    static::$prettify    = ($prettify) ?: static::$prettify;

    static::initExchange($api_name);
  }

  /**
   * Init API HTTP exchange log
   *
   * @param string $api_name API method name
   *
   * @return void
   * @throws \Exception
   */
  static function initExchange($api_name) {
    $http_exchange                = new CExchangeHTTP();
    $http_exchange->date_echange  = CMbDT::dateTime();
    $http_exchange->emetteur      = CUser::get()->user_username;
    $http_exchange->destinataire  = CAppUI::conf('mb_id');
    $http_exchange->function_name = "mb sync - {$api_name}";
    $http_exchange->input         = serialize(
      [
        'GET'  => CMbSecurity::filterInput($_GET),
        'POST' => file_get_contents('php://input'),
      ]
    );

    $http_exchange->store();

    CApp::$chrono->stop();

    $http_exchange_chrono = new Chronometer();
    $http_exchange_chrono->start();

    static::$http_exchange_chrono = $http_exchange_chrono;
    static::$http_exchange        = $http_exchange;
  }

  /**
   * Check if a mandatory parameter is here
   *
   * @param string $needle   Parameter name
   * @param array  $haystack To search from
   *
   * @return void
   * @throws \Exception
   */
  static function checkMandatoryParameter($needle, $haystack) {
    if (!$haystack || !isset($haystack[$needle]) || !$haystack[$needle]) {
      static::response('common-error-Missing parameter: %s', 412, null, $needle);
    }
  }

  /**
   * No permission error message
   *
   * @return void
   * @throws \Exception
   */
  static function noPermissionError() {
    static::response('common-error-No permission', 410);
  }

  /**
   * Object not found error message
   *
   * @return void
   * @throws \Exception
   */
  static function objectNotFoundError() {
    static::response('common-error-Object not found', 404);
  }

  /**
   * Invalid parameter error message
   *
   * @param string $value Value
   *
   * @return void
   * @throws \Exception
   */
  static function invalidParameterError($value) {
    static::response('common-error-Invalid parameter: %s', 412, null, $value);
  }

  /**
   * Formats a JSON message
   *
   * @param string       $msg    Message to send
   * @param integer      $code   Return code
   * @param null|integer $id     [optional] Additional information
   * @param array        $params [optional] Additional parameters
   * @param mixed        $_      [optional] Any number of printf-like parameters to be applied
   *
   * @return array
   */
  static function getMessage($msg, $code = 200, $id = null, $params = array(), $_ = null) {
    $args = func_get_args();

    $message = [
      'msg'  => CAppUI::tr($msg, array_slice($args, 3)),
      'code' => $code,
      'id'   => $id
    ];

    if ($params) {
      foreach ($params as $_k => $_v) {
        $message[$_k] = $_v;
      }
    }

    $status = static::getResponseStatus($code);
    if (!$status) {
      CAppUI::stepAjax('common-error-Invalid parameter: %s', UI_MSG_ERROR, $code);
    }

    $message['status'] = $status;

    return $message;
  }

  /**
   * Sends a JSON message and quit
   *
   * @param string       $msg    Message to send
   * @param integer      $code   Return code
   * @param null|integer $id     [optional] Additional information
   * @param array        $params [optional] Additional parameters
   * @param mixed        $_      [optional] Any number of printf-like parameters to be applied
   *
   * @return void
   * @throws \Exception
   */
  static function response($msg, $code = 200, $id = null, $params = array(), $_ = null) {
    $args = func_get_args();

    // Because of getMessage() variadic function
    $message = call_user_func_array('static::getMessage', $args);

    static::json($message, $code);
  }

  /**
   * Outputs JSON data after removing the Output Buffer
   *
   * @param object|array $data The data to output
   * @param integer      $code Return code
   *
   * @return void
   * @throws \Exception
   */
  static function json(&$data, $code = 200) {
    $pretty_print = static::$prettify ? JSON_PRETTY_PRINT : null;

    $msg  = ['code' => $code];
    $data = array_merge($msg, $data);

    $json = CMbArray::toJSON($data, true, $pretty_print);

    static::$http_exchange_chrono->stop();
    CApp::$chrono->start();

    $http_exchange = static::$http_exchange;

    if ($http_exchange && $http_exchange->_id) {
      $http_exchange->response_time = static::$http_exchange_chrono->total;
      $http_exchange->http_fault    = (static::getResponseStatus($code) == 'error') ? '1' : '0';
      $http_exchange->output        = serialize($json);
      $http_exchange->store();
    }

    ob_clean();

    header('HTTP/1.0 200');
    header('Content-Type: application/json; charset=utf-8');

    echo $json;
    CApp::rip();
  }

  /**
   * Checks the API version
   *
   * @return bool
   */
  static function getAPIVersion() {
    return (static::$api_version) ?: 1;
  }

  /**
   * Get AppFine preference spec
   *
   * @param string $pref CPreference name
   *
   * @return string
   */
  static function getPrefSpec($pref) {
    return 'str';
  }
}
