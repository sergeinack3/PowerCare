<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Api;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CAPIObject
 */
class CAPIObject implements IShortNameAutoloadable {
  static $classes = array(
    'CAPICMediusers',
    'CAPICFunctions',
  );

  static $fields = array();
  static $refs = array();

  /** @var CMbObject */
  public $_ref_object;

  /**
   * Update some fields
   *
   * @return void
   */
  function updateFields() {
  }

  /**
   * Load CAPIObject references
   *
   * @return void
   */
  function loadRefs() {
    foreach (static::$refs as $_name => $_ref) {
      $this->{$_name} = $this->loadRef($_name);
    }
  }

  /**
   * Load CAPIObject references for API purposes
   *
   * @return void
   */
  function loadAPIRefs() {
    $this->loadRefs();
  }

  /**
   * Load a CAPIObject reference
   *
   * @param string $name Reference name
   *
   * @return CMbObject
   */
  function loadRef($name) {
    $ref = static::$refs[$name];

    $class = key($ref);
    $field = $ref[$class];

    if (!$this->{$field}) {
      return null;
    }

    /** @var CMbObject $object */
    $object = new $class();
    $object->load($this->{$field});

    return self::mbObjectToAPI($object);
  }

  /**
   * Loads API objects not directly related to current object
   *
   * @return array
   */
  function loadExternalsRefs() {
    return $refs = array();
  }

  /**
   * Map a CMbObject into an API object
   *
   * @param CMbObject $object Mediboard object
   *
   * @return CAPIObject
   */
  static function mbObjectToAPI(CMbObject $object) {
    $class = self::getAPIClass($object->_class);

    /** @var CAPIObject $api_object */
    $api_object = new $class();

    foreach ($api_object::$fields as $_object_field => $_api_field) {
      $api_object->{$_api_field} = utf8_encode($object->{$_object_field});
    }

    $api_object->loadAPIRefs();
    $api_object->updateFields();

    // Not needed here, or it will be printed or transmitted
    unset($api_object->_ref_object);

    return $api_object;
  }

  /**
   * Bind a CAPIObject from array
   *
   * @param array $params Array of field => value
   *
   * @return void
   */
  function bind(array &$params) {
    foreach ($params as $_field => $_param) {
      $this->{$_field} = utf8_decode(trim($_param));
    }
  }

  /**
   * Map an API object into a CMbObject
   *
   * @return CMbObject
   */
  function APItoMbObject() {
    $class_map = CClassMap::getInstance();
    $short = $class_map->getShortName(get_called_class());

    $object_class = substr($short, 4);

    /** @var CMbObject $object */
    $object = new $object_class();
    foreach (static::$fields as $_object_field => $_api_field) {
      $object->{$_object_field} = $this->{$_api_field};
    }

    return $this->_ref_object = $object;
  }

  /**
   * Store a CMbObject from a CSyncObject
   *
   * @param array|null $params Array of field => value
   *
   * @throws Exception
   * @return mixed
   */
  function storeMbObject(array &$params = null) {
    if ($params) {
      $this->bind($params);
    }

    $this->_ref_object = $this->APItoMbObject();
    $this->_ref_object->updateFormFields();

    if ($msg = $this->_ref_object->store()) {
      throw new Exception($msg);
    }

    return true;
  }

  /**
   * Get the correct class API of a given object
   *
   * @param string $object_class CSync object class
   *
   * @return string
   */
  static function getAPIClass($object_class) {
    $class = "CAPI{$object_class}";

    if (!in_array($class, self::$classes)) {
      CAppUI::stepAjax('common-error-Invalid parameter', UI_MSG_ERROR);
    }

    return $class;
  }

  /**
   * Stores API external ID
   *
   * @return null|string
   */
  function storeExtID() {
    $object   = $this->_ref_object;
    $username = CMediusers::get()->_user_username;

    $id_ext = CIdSante400::getMatch($object->_class, 'api_creation', $username, $object->_id);

    if (!$id_ext || !$id_ext->_id) {
      $id_ext = new CIdSante400();
      $id_ext->setObject($object);

      $id_ext->tag   = 'api_creation';
      $id_ext->id400 = $username;

      return $id_ext->store();
    }
  }
}
