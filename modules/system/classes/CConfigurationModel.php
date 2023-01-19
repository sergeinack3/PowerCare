<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CRequest;

/**
 * Abstract class of a CConfiguration model node
 */
abstract class CConfigurationModel implements IShortNameAutoloadable {
  /** @var string Module */
  protected $module;

  /** @var string Context class */
  protected $context_class;

  /** @var string Inherit key */
  protected $inherit;

  /** @var string Sanitized inherit key */
  protected $sanitized_inherit;

  /** @var array Model */
  protected $model = array();

  /** @var array List of object IDs */
  public $ids = array();

  /**
   * CConfigurationModel constructor.
   *
   * @param string $module        Module name
   * @param string $context_class Context class
   * @param string $inherit       Inherit key
   */
  public function __construct($module, $context_class, $inherit) {
    $this->setModule($module)->setContextClass($context_class)->setInherit($inherit);
    $this->init();
  }

  /**
   * Initialize the Model object
   *
   * @return void
   */
  protected function init() {

  }

  /**
   * Module accessor
   *
   * @return string
   */
  public function getModule() {
    return $this->module;
  }

  /**
   * Module setter
   *
   * @param string $module Module name
   *
   * @return $this
   */
  public function setModule($module) {
    $this->module = $module;

    return $this;
  }

  /**
   * Context class getter
   *
   * @return string
   */
  public function getContextClass() {
    return $this->context_class;
  }

  /**
   * Context class setter
   *
   * @param string $context_class Class of the context
   *
   * @return $this
   */
  public function setContextClass($context_class) {
    $this->context_class = $context_class;

    return $this;
  }

  /**
   * Inherit key getter
   *
   * @return string
   */
  public function getInherit() {
    return $this->inherit;
  }

  /**
   * Inherit key setter
   *
   * @param string $inherit Inherit key
   *
   * @return $this
   */
  public function setInherit($inherit) {
    $this->inherit = $inherit;

    return $this;
  }

  /**
   * Model accessor
   *
   * @return array
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * Set the model to node
   *
   * @param array $model Model
   *
   * @return $this
   */
  public function setModel(array &$model) {
    $this->model = $model;

    return $this;
  }

  /**
   * Merge a model to current one
   *
   * @param array $model Model to append
   *
   * @return void
   */
  public function mergeModel(array &$model) {
    $this->model = array_merge_recursive($this->model, $model);
  }

  /**
   * Sanitized inherit accessor
   *
   * @return string
   */
  public function getSanitizedInherit() {
    return $this->sanitized_inherit;
  }

  /**
   * Set the sanitized inherit
   *
   * @param string $sanitized_inherit Sanitized inherot
   *
   * @return $this
   */
  public function setSanitizedInherit($sanitized_inherit) {
    $this->sanitized_inherit = $sanitized_inherit;

    return $this;
  }

  /**
   * Getter for object ids
   *
   * @return array
   */
  public function getObjectIDs() {
    return $this->ids;
  }

  /**
   * Object ids setter
   *
   * @param array $ids Array of ids
   *
   * @return $this
   */
  public function setObjectIDs($ids = array()) {
    $this->ids = $ids;

    return $this;
  }

  /**
   * Load object ids for current leaf
   *
   * @return void
   */
  public function loadObjectIDs() {
    $_class = $this->getContextClass();

    $_obj = new $_class();
    $_ds  = $_obj->getDS();

    $_request = new CRequest();
    $_request->addSelect("{$_obj->_spec->key} AS id");
    $_request->addTable($_obj->_spec->table);

    $this->setObjectIDs($_ds->loadColumn($_request->makeSelect()));
  }
}
