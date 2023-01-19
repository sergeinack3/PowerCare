<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Exception;
use Ox\AppFine\Server\Exception\CAppFineException;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CValue;

/**
 * Class CAPIDispatcher
 */
class CAPIDispatcher implements IShortNameAutoloadable {
  static private $callstack = [];
  static private $response = [];
  // todo to remove
  /**
   * @param array $post Mobile queries
   *
   * @return array
   * @throws CMbException
   */
  static public function dispatch($post = []) {
    $api_version  = CValue::read($post, 'api_version', null);
    $product_name = CValue::read($post, 'product_name', null);

    if (!$api_version) {
      throw new CMbException('CAPI-error-No API version provided');
    }

    if (!$product_name) {
      throw new CMbException('CAPI-error-No product name provided');
    }

    $calls = CValue::read($post, 'calls', []);

    /** @var CAPI $product_api */
    $product_api = "CAPI{$product_name}";

    if (!class_exists($product_api)) {
      throw new CMbException("CAPI-error-Invalid product name");
    }

    $product_api::checkPerm();

    //Récupération de toutes les classes d'API du projet
    //$api_classes = $product_api::getAPIClasses();

    foreach ($calls as $_call) {
      $_call_id        = CValue::read($_call, 'call_id', null);
      $_parent_call_id = CValue::read($_call, 'parent_call_id', null);
      $_command        = CValue::read($_call, 'command', null);

      if (!$_call_id) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Call ID not found', 412));
        continue;
      }

      static::$callstack[$_call_id] = [];

      if ($_parent_call_id && !isset(static::$callstack[$_parent_call_id])) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Parent call ID provided but not found', 412), $_call_id);
        continue;
      }

      if (!$_command) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Command not found', 412), $_call_id);
        continue;
      }

      $api_class = $product_api::getAPIClass($_command);

      if (!$api_class) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Command not found', 412), $_call_id);
        continue;
      }

      /** @var CAPI $api */
      $api      = new $api_class($_call, $api_version);
      $api_name = $api->getAPIName();

      // Nested API calls
      if ($_parent_call_id) {
        static::$callstack[$_call_id] = array_merge(static::$callstack[$_parent_call_id], static::$callstack[$_call_id]);
        $api->setParentStack(static::$callstack[$_call_id]);
      }

      try {
        static::$callstack[$_call_id] = array_merge(static::$callstack[$_call_id], array($api->getTempID() => $api->run()));
      }
      catch (Exception $e) {
        static::pushResponse(
          $api_name,
          static::getMessage($e->getMessage(), $e->getCode()),
          $api->_call_id
        );
        continue;
      }

      static::pushResponse($api_name, $api->getAPIResponse());
    }

    return static::$response;
  }

  /**
   * @param array $post Mobile queries
   *
   * @return array
   * @throws CAppFineException
   */
  static public function dispatchNew($post = []) {
    $api_version  = CValue::read($post, 'api_version', null);
    $product_name = CValue::read($post, 'product_name', null);

    if (!$api_version) {
      throw new CAppFineException(0, 500, 'CAPI-error-No API version provided');
    }

    if (!$product_name) {
      throw new CAppFineException(0, 500, 'CAPI-error-No product name provided');
    }

    $calls = CValue::read($post, 'calls', []);

    $product_api = "CAPI{$product_name}";

    if (!class_exists($product_api)) {
      throw new CAppFineException(0, 500, "CAPI-error-Invalid product name");
    }

    $product_api::checkPerm();

    //Récupération de toutes les classes d'API du projet
    //$api_classes = $product_api::getAPIClasses();

    foreach ($calls as $_call) {
      $_call_id        = CValue::read($_call, 'call_id', null);
      $_parent_call_id = CValue::read($_call, 'parent_call_id', null);
      $_command        = CValue::read($_call, 'command', null);

      if (!$_call_id) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Call ID not found', 412));
        continue;
      }

      static::$callstack[$_call_id] = [];

      if ($_parent_call_id && !isset(static::$callstack[$_parent_call_id])) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Parent call ID provided but not found', 412), $_call_id);
        continue;
      }

      if (!$_command) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Command not found', 412), $_call_id);
        continue;
      }

      $api_class = $product_api::getAPIClass($_command);

      if (!$api_class) {
        static::pushResponse($_command, static::getMessage('CAPI-error-Command not found', 412), $_call_id);
        continue;
      }

      /** @var CAPI $api */
      $api      = new $api_class($_call, $api_version);
      $api_name = $api->getAPIName();

      // Nested API calls
      if ($_parent_call_id) {
        static::$callstack[$_call_id] = array_merge(static::$callstack[$_parent_call_id], static::$callstack[$_call_id]);
        $api->setParentStack(static::$callstack[$_call_id]);
      }

      try {
        static::$callstack[$_call_id] = array_merge(static::$callstack[$_call_id], array($api->getTempID() => $api->runNew()));
      }
      catch (CAppFineException $e) {
        static::pushResponse(
          $api_name,
          static::getMessage($e->getMessage(), $e->getCode()),
          $api->_call_id
        );
        continue;
      }

      static::pushResponse($api_name, $api->getAPIResponse());
    }

    return static::$response;
  }

  /**
   * Add response to stack
   *
   * @param string  $name     Response name
   * @param array   $response Response content
   * @param integer $call_id  [optional] Call id
   *
   * @return void
   */
  static function pushResponse($name, $response, $call_id = null) {
    $response = array_merge($response, ['api_name' => $name]);

    if ($call_id) {
      $response = array_merge($response, ['call_id' => $call_id]);
    }

    static::$response[] = $response;
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
  static function getMessage($msg, $code = 200, $id = null, $params = [], $_ = null) {
    $args = func_get_args();

    $message = [
      'msg'  => CAppUI::tr($msg, array_slice($args, 3)),
      'code' => $code,
      'id'   => $id,
    ];

    if ($params) {
      foreach ($params as $_k => $_v) {
        $message[$_k] = $_v;
      }
    }

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
   */
  static function response($msg, $code = 200, $id = null, $params = [], $_ = null) {
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
   */
  static public function json(&$data, $code = 200) {
    $msg = [
      'code'  => $code,
      'calls' => $data,
    ];

    CApp::json($msg);
  }

  /**
   * Outputs JSON data to prevent user
   *
   * @param string $msg   Message content
   * @param string $title Message title
   *
   * @return void;
   */
  static public function errorWithMessage($msg, $title = null) {
    $res = [
      'code'  => 450,
      'msg'   => $msg,
      'title' => $title,
    ];

    CApp::json($res);
  }
}
