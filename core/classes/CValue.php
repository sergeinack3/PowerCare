<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * A class used to get/set data from $_POST, $_GET, or $_SESSION
 */
abstract class CValue {

  /**
   * Utility function to return a value from a named array or a specified default
   * array should always be passed by reference
   * 
   * @param array  $array   The array containing the values
   * @param string $name    The key of the value to get
   * @param mixed  $default The value if the key's value doesn't exist
   * 
   * @return mixed The value corresponding to $name in $array
   */
  static function read(&$array, $name, $default = null) {
    return isset($array[$name]) ? $array[$name] : $default;
  }

  /**
   * Returns the first argument that do not evaluate to false (0, null, "", ...)
   * 
   * @return mixed The first value evaluated to TRUE
   */
  static function first(){
    foreach (func_get_args() as $v) {
      if ($v) {
        return $v;
      }
    }
  }

  /**
   * Returns the value of a variable retrieved from HTTP GET, with at least a default value
   * 
   * @param string $name    The key of the value to get from HTTP GET
   * @param string $default The default value in the key's value doesn't exist
   *
   * @deprecated see CView::get()
   *
   * @return mixed The value associated to $name in the HTTP GET
   */
  static function get($name, $default = null) {
    return isset($_GET[$name]) ? $_GET[$name] : $default;
  }
  
  /**
   * Returns the value of a variable retrieved from HTTP POST, with at least a default value
   * 
   * @param string $name    The key of the value to get from HTTP POST
   * @param string $default The default value in the key's value doesn't exist
   *
   * @deprecated see CView::post()
   *
   * @return mixed The value associated to $name in the HTTP POST
   */
  static function post($name, $default = null) {
    return isset($_POST[$name]) ? $_POST[$name] : $default;
  }

  /**
   * Unset POST parameters begining with name value
   *
   * @param string $name  The key to search for
   * @param bool   $exact Wether
   *
   * @return bool|array
   */
  static public function unsetPost($name, $exact = false) {
    foreach ($_POST as $k => $v) {
      if (($exact && $k === $name) || (substr($k, 0, strlen($name)) === $name)) {
        unset($_POST[$k]);
      }
    }
    return $_POST;
  }


  /**
   * Get all POST parameters
   *
   * @return bool|array
   */
  static public function allPost() {
    return !(empty($_POST)) ? $_POST : null;
  }
  
  /**
   * Returns the value of a variable retrieved from HTTP REQUEST (POST or GET), with at least a default value
   *
   * @param string $name    The key of the value to get from HTTP REQUEST (POST or GET)
   * @param string $default The default value in the key's value doesn't exist
   *
   * @deprecated see CView::request
   *
   * @return mixed The value associated to $name in the HTTP REQUEST (POST or GET)
   */
  static function request($name, $default = null) {
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
  }
  
  /**
   * Returns the value of a FILE variable retrieved from HTTP POST
   * 
   * @param string $name The FILE key of the value to get from HTTP POST
   * 
   * @return mixed The FILE value associated to $name in the HTTP REQUEST (POST or GET)
   */
  static function files($name) {
    return isset($_FILES[$name]) ? $_FILES[$name] : null;
  }
  

  /**
   * Returns the value of a variable retrieved from Session, with at least a default value
   * 
   * @param string $name    The key of the value to get from Session
   * @param string $default The default value in the key's value doesn't exist
   * 
   * @return mixed The value associated to $name in the Session
   */
  static function session($name, $default = null) {
    global $m;
    return self::read($_SESSION[$m], $name, $default);
  }

  /**
   * Returns the value of a variable retrieved from Session, with at least a default value
   *
   * @param string $name    The key of the value to get from Session
   * @param string $default The default value in the key's value doesn't exist
   *
   * @return mixed The value associated to $name in the Session
   */
  static function sessionAbs($name, $default = null) {
    return self::read($_SESSION, $name, $default);
  }
  
  /**
   * Returns a value from the client's cookies, with at least a default value
   * 
   * @param string $name    The key of the value to get from the cookies
   * @param string $default The default value in the key's value doesn't exist
   * 
   * @return mixed The value associated to $name in the cookies
   */
  static function cookie($name, $default = null) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
  }

  /**
   * Returns the value of a variable retrieved from HTTP GET or Session, relative  to a module ($m), 
   * with at least a default value. Stores it in $_SESSION in all cases, with at least a default value.
   * 
   * @param string $name    The key of the value to get from HTTP GET or Session
   * @param string $default The default value in the key's value doesn't exist
   *
   * @deprecated see CView::get()
   *
   * @return mixed The value associated to $name in the HTTP GET or Session
   */
  static function getOrSession($name, $default = null) {
    global $m;
  
    if (isset($_GET[$name])) {
      $_SESSION[$m][$name] = $_GET[$name];
    }
    
    return self::read($_SESSION[$m], $name, $default);
  }

  /**
   * Returns the value of a variable retrieved from HTTP POST or Session, relative to a module ($m), 
   * with at least a default value. Stores it in $_SESSION in all cases, with at least a default value.
   * 
   * @param string $name    The key of the value to get from HTTP POST or Session
   * @param string $default The default value in the key's value doesn't exist
   *
   * @deprecated see CView::post()
   *
   * @return mixed The value associated to $name in the HTTP POST or Session
   */
  static function postOrSession($name, $default = null) {
    global $m;
  
    if (isset($_POST[$name])) {
      $_SESSION[$m][$name] = $_POST[$name];
    }
    
    return self::read($_SESSION[$m], $name, $default);
  }

  /**
   * Returns the value of a variable retrieved from HTTP GET or Session, with at least a
   * default value. Stores it in $_SESSION in all cases, with at least a default value.
   * 
   * @param string $name    The key of the value to get from HTTP GET or Session
   * @param string $default The default value in the key's value doesn't exist
   * 
   * @return mixed The value associated to $valName in the HTTP GET or Session
   */
  static function getOrSessionAbs($name, $default = null) {
    if (isset($_GET[$name])) {
      $_SESSION[$name] = $_GET[$name];
    }
    
    return self::read($_SESSION, $name, $default);
  }
  
  /**
   * Returns the value of a variable retrieved from HTTP POST or Session, with at least a
   * default value. Stores it in $_SESSION in all cases, with at least a default value.
   * 
   * @param string $name    The key of the value to get from HTTP POST or Session
   * @param string $default The default value in the key's value doesn't exist
   * 
   * @return mixed The value associated to $name in the HTTP POST or Session
   */
  static function postOrSessionAbs($name, $default = null) {
    if (isset($_POST[$name])) {
      $_SESSION[$name] = $_POST[$name];
    }
    
    return self::read($_SESSION, $name, $default);
  }

  /**
   * Sets a value to the session[$m]. Very useful to nullify object ids after deletion
   * 
   * @param string $name  The key to store in the session
   * @param mixed  $value The value to store
   *
   * @deprecated see CView::setSession()
   *
   * @return mixed The value
   */
  static function setSession($name, $value = null) {
    global $m;
    return $_SESSION[$m][$name] = $value;
  }

  /**
   * Sets a value to the session. Very useful to nullify object ids after deletion
   * 
   * @param string $name  The key to store in the session
   * @param mixed  $value The value to store
   * 
   * @return mixed The value
   */
  static function setSessionAbs($name, $value = null) {
    return $_SESSION[$name] = $value;
  }
}
