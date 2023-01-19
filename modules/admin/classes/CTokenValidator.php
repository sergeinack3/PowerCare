<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;
use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * Description
 */
abstract class CTokenValidator implements IShortNameAutoloadable {
  protected $token_params = array();

  protected $patterns = array(
    'get'  => array(),
    'post' => array(),
  );

  protected $authorized_methods = array();

  /**
   * HTTP methods authorized
   *
   * @return array
   */
  protected function getAuthorizedMethods() {
    return $this->authorized_methods;
  }

  /**
   * Can token uses HTTP method?
   *
   * @param string $method HTTP method
   *
   * @return bool
   */
  protected function canMethod($method = 'get') {
    return in_array($method, $this->getAuthorizedMethods());
  }

  /**
   * Get all authorized patterns
   *
   * @param null $method HTTP Method
   *
   * @return array|bool|mixed
   */
  protected function getPatterns($method = null) {
    if ($method && !$this->canMethod($method)) {
      return array();
    }

    $patterns = $this->updatePatterns();

    return ($method && array_key_exists($method, $patterns)) ? $patterns[$method] : $patterns;
  }

  /**
   * Apply update to authorized patterns
   *
   * @return array
   */
  protected function updatePatterns() {
    return $this->patterns;
  }

  /**
   * Checks token parameters within given method
   *
   * @param array $params Given parameters to check
   *
   * @return array
   */
  protected function checkParams($params = array()) {
    if (!$params || !is_array($params)) {
      return array();
    }

    $patterns = $this->getPatterns();

    $checked_params = array();

    foreach ($this->getAuthorizedMethods() as $_method) {
      $checked_params[$_method] = array();
      $_patterns                = $patterns[$_method];

      foreach ($params as $_param => $_value) {
        if (!array_key_exists($_param, $_patterns)) {
          continue;
        }

        if (is_array($_patterns[$_param])) {
          // Default set values
          [$_pattern, $_default] = $_patterns[$_param];
          $checked_params[$_method][$_param] = $_default;
        }
        else {
          $_pattern = $_patterns[$_param];
        }

        if (preg_match("{$_pattern}", $_value, $matches)) {
          $checked_params[$_method][$_param] = reset($matches);
        }
      }

      // Setting default unset values
      foreach ($_patterns as $_field => $_pattern) {
        if (!is_array($_pattern)) {
          continue;
        }

        [$_value, $_default] = $_pattern;

        if (!array_key_exists($_field, $checked_params[$_method]) && ($_default !== false)) {
          $checked_params[$_method][$_field] = $_default;
        }
      }
    }

    // Token parameters applied if not in validator patterns
    if ($this->token_params && isset($checked_params['get'])) {
      $checked_params['get'] = array_merge($this->token_params, $checked_params['get']);
    }

    return $checked_params;
  }

  /**
   * Clear original parameters and set validated ones
   *
   * @param array $params Parameters
   *
   * @return array
   */
  protected function prepareParams($params = array()) {
    $get_params  = $_GET;
    $post_params = $_POST;

    $_GET     = array();
    $_POST    = array();
    $_REQUEST = array();
    $_COOKIE  = array();
    $_FILES   = array();

    // Token params -> GET params -> POST params
    $params = array_merge($params, $get_params, $post_params);

    return $checked_params = $this->checkParams($params);
  }

  /**
   * Checks and applies HTTP parameters
   *
   * @param array $params CViewAccessToken default parameters
   *
   * @return void
   */
  function applyParams($params = array()) {
    $this->token_params = $params;
    $checked_params     = $this->prepareParams($params);

    foreach ($checked_params as $_method => $_params) {
      switch ($_method) {
        case 'get':
          $_GET = $_params;
          break;

        case 'post':
          $_POST = $_params;
          break;

        default:
          break;
      }
    }

    $_REQUEST = array_merge($_GET, $_POST);
  }

  /**
   * Get HTTP GET query
   *
   * @param array $params Parameters
   *
   * @return null|string
   */
  function getQueryString($params = array()) {
    $checked_params = $this->prepareParams($params);

    if (!isset($checked_params['get'])) {
      return null;
    }

    return http_build_query($checked_params['get'], null, '&');
  }
}
