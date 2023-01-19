<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\Sessions\CSessionHandler;

/**
 * The view history class
 */
class CViewHistory {
  const SESSION_KEY = "navigation_history";

  const TYPE_VIEW   = "view";
  const TYPE_EDIT   = "edit";
  const TYPE_SEARCH = "search";
  const TYPE_NEW    = "new";

  /** @var string */
  public $m;

  /** @var string */
  public $action;

  /** @var string */
  public $actionType;

  /** @var string */
  public $datetime;

  /** @var string */
  public $object_guid;

  /** @var CMbObject */
  private $object;

  /** @var array */
  public $params;

  /** @var string */
  public $type;

  /**
   * History constructor
   *
   * @param string    $m          Module
   * @param string    $action     Action
   * @param string    $actionType Action type
   * @param CMbObject $object     Object
   * @param array     $params     Parameters
   * @param string    $type       Type of view
   */
  protected function __construct($m, $action, $actionType, CMbObject $object, $params, $type) {
    $this->datetime    = CMbDT::dateTime();
    $this->m           = $m;
    $this->action      = $action;
    $this->actionType  = $actionType;
    $this->params      = $params;
    $this->type        = $type;
    $this->object_guid = $object->_guid;
    $this->object      = $object;
  }

  /**
   * Serialize callback
   *
   * @return array
   */
  function __sleep() {
    $vars = get_object_vars($this);
    unset($vars["object"]);
    return array_keys($vars);
  }

  /**
   * Get target object
   *
   * @return CMbObject
   */
  function getObject(){
    if (!$this->object) {
      $this->object = CStoredObject::loadFromGuid($this->object_guid);
    }

    if ($this->object) {
      $this->object->loadComplete();
    }

    return $this->object;
  }

  /**
   * Get unique key
   *
   * @return string
   */
  function getKey() {
    $params = null;
    if ($this->params) {
      $params = array_filter(
        $this->params,
        function ($value) {
          return $value !== "" && $value !== null;
        }
      );

      ksort($params);
    }

    return md5("$this->m/$this->action/$this->actionType/{$this->object_guid}/".serialize($params));
  }

  /**
   * Save history entry
   *
   * @param CMbObject $object Object
   * @param string    $type   Type of view
   * @param array     $params Parameters
   *
   * @return void
   */
  static function save(CMbObject $object, $type = self::TYPE_VIEW, $params = array()) {
    global $m, $action, $actionType, $suppressHeaders, $ajax, $dialog;

    if ($suppressHeaders || $ajax || $dialog) {
      return;
    }

    $length = CAppUI::pref("navigationHistoryLength");
    if ($length <= 0) {
      return;
    }

    $entry = new self($m, $action, $actionType, $object, $params, $type);
    $key = $entry->getKey();

    $history = self::getHistory();

    $history_key = CValue::get("_history_key");
    if ($history_key) {
      unset($history[$history_key]);
    }

    $history[$key] = $entry;

    if (count($history) > min(20, $length)) {
      array_shift($history);
    }

    $is_open = CSessionHandler::isOpen();
    CSessionHandler::start();
    $_SESSION[self::SESSION_KEY] = $history;
    if (!$is_open) {
      CSessionHandler::writeClose();
    }
  }

  /**
   * Get history entry URL
   *
   * @return string
   */
  function getURL() {
    $params = array(
      "m"               => $this->m,
      $this->actionType => $this->action,
    );

    if ($this->params) {
      $params = array_merge($params, $this->params);
    }


    $params["_history_key"] = $this->getKey();

    return http_build_query($params, null, "&");
  }

  /**
   * Get tab title
   *
   * @return string
   */
  function getTabName(){
    return CAppUI::tr("mod-$this->m-tab-$this->action");
  }

  /**
   * Get history entries
   *
   * @param bool $reverse Return in reversed order
   *
   * @return self[]
   */
  static function getHistory($reverse = false) {
    $history = CValue::read($_SESSION, self::SESSION_KEY);

    if ($reverse && $history) {
      $history = array_reverse($history);
    }

    return $history;
  }
}