<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Api;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbString;

/**
 * Class CAPITools
 */
class CAPITools implements IShortNameAutoloadable {
  static $http_codes = array(
    '200' => 'OK',
    '201' => 'Created',
    '210' => 'Duplicate data',
    '400' => 'Bad Request',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '505' => 'HTTP Version not supported'
  );

  static $codes_statuses = array(
    '200' => 'ok',
    '201' => 'ok',
    '210' => 'error',
    '400' => 'error',
    '403' => 'error',
    '404' => 'error',
    '500' => 'error',
    '501' => 'error',
    '505' => 'error'
  );

  /**
   * Formats a JSON message
   *
   * @param string       $msg      Message to send
   * @param integer      $code     Return code
   * @param boolean      $prettify [optional] JSON_PRETTY_PRINT
   * @param null|integer $id       [optional] Additional information
   * @param array        $params   [optional] Additional parameters
   * @param mixed        $_        [optional] Any number of printf-like parameters to be applied
   *
   * @return array
   */
  static function getMessage($msg, $code = 200, $prettify = false, $id = null, $params = array(), $_ = null) {
    $args = func_get_args();

    $message = array(
      'msg'  => utf8_encode(CAppUI::tr($msg, array_slice($args, 4))),
      'code' => $code,
      'id'   => $id
    );

    if ($params) {
      foreach ($params as $_k => $_v) {
        $message[$_k] = $_v;
      }
    }

    if (!isset(self::$codes_statuses[$code])) {
      CAppUI::stepAjax('common-error-Invalid parameter: %s', UI_MSG_ERROR, $code);
    }

    $message['status'] = self::$codes_statuses[$code];

    return $message;
  }

  /**
   * Sends a JSON message and quit
   *
   * @param string       $msg      Message to send
   * @param integer      $code     Return code
   * @param boolean      $prettify [optional] JSON_PRETTY_PRINT
   * @param null|integer $id       [optional] Additional information
   * @param array        $params   [optional] Additional parameters
   * @param mixed        $_        [optional] Any number of printf-like parameters to be applied
   *
   * @return void
   */
  static function response($msg, $code = 200, $prettify = false, $id = null, $params = array(), $_ = null) {
    $args = func_get_args();

    // Because of getMessage() variadic function
    $message = call_user_func_array(array("self", "getMessage"), $args);

    self::json($message, $code, $prettify);
  }

  /**
   * Outputs JSON data after removing the Output Buffer
   *
   * @param object|array $data     The data to output
   * @param integer      $code     Return code
   * @param boolean      $prettify JSON_PRETTY_PRINT
   *
   * @return null
   */
  static function json(&$data, $code = 200, $prettify = false) {
    $pretty_print = $prettify ? JSON_PRETTY_PRINT : null;

    $json = CMbArray::toJSON($data, true, $pretty_print);

    if ($prettify) {
      $json = CMbString::highlightCode('javascript', CMbArray::toJSON($data, true, $pretty_print), true, 'max-height: 600px;');
    }

    ob_clean();

    $http_code = self::$http_codes[$code];
    header("HTTP/1.0 {$code} {$http_code}");
    header('Content-Type: application/json');

    echo $json;
    CApp::rip();
  }

  static function makeAPIfield($type = 'text', $mandatory = false, $default = null, $enum = array()) {
    switch ($type) {
      case 'text':
      case 'password':
      case 'num':
      case 'date':
      case 'time':
      case 'bool':
        return array(
          'type'      => $type,
          'mandatory' => $mandatory
        );

      case 'select':
        return array(
          'type'      => $type,
          'mandatory' => $mandatory,
          'default'   => $default,
          'enum'      => $enum
        );

      default:
    }

    return null;
  }
}
