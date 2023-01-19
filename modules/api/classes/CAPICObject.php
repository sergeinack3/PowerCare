<?php
/**
 * @package Mediboard\Api
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Api;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Description
 */
abstract class CAPICObject  implements IShortNameAutoloadable {
  const TMP_TAG = 'mb_sync_tmp_id';
  const CREATION_TAG = 'mb_sync_creation';
  const UPDATE_TAG = 'mb_sync_update';

  static $fields = [];
  static $refs = [];
  static $backrefs = [];

  /** @var CStoredObject */
  public $_ref_object;

  /** @var string Temporary ID in order to prevent connectivity issues */
  public $_tmp_id;

  static $load_lite = false;

  // Do not change order
  static $classes = [];

  /**
   * Get the object to send via API
   *
   * @param CSyncLog $object $classes object
   *
   * @return CAPICObject|bool
   * @throws Exception
   */
  static function getAPIObject(CSyncLog $object) {
    $class = static::getAPIClass($object->object_class);

    /** @var CAPICObject $api_object */
    $api_object = new $class();
    $map        = $api_object->map($object);

    /**
     * Difference between false and null in map()
     * FALSE: Object deleted
     * NULL: Cannot synchronize object
     */
    if (!$map) {
      return $map;
    }

    $api_object->loadAPIRefs();
    $api_object->updateFields();

    // Not needed here, or it will be printed or transmitted
    unset($api_object->_ref_object);

    return $api_object;
  }

  /**
   * Get the correct class API of a given object
   *
   * @param string $object_class CSync object class
   *
   * @return string
   */
  static function getAPIClass($object_class) {
    return null;
  }

  /**
   * Map a CSyncLog into an API object
   *
   * @param CSyncLog $object CSyncLog object
   *
   * @return bool
   */
  function map(CSyncLog $object) {
    $target = $object->loadTargetObject(false);

    if (!$target || !$target->_id) {
      return false;
    }

    if (!$this->canSync($target)) {
      return null;
    }

    foreach (static::$fields as $_target => $_field) {
      $this->{$_field} = $target->{$_target};
    }

    return true;
  }

  /**
   * Checks if we can sync this object according to redefined parameters
   *
   * @param CStoredObject $object Object
   *
   * @return bool
   */
  function canSync(CStoredObject $object) {
    return true;
  }

  /**
   * Get fields that can be updated
   *
   * @return array
   */
  static function getFieldsToUpdate() {
    $fields = static::$fields;

    if (!$fields) {
      return [];
    }

    // Removing primary ID field
    return array_diff($fields, ['id']);
  }

  /**
   * Update some fields
   *
   * @return void
   */
  function updateFields() {
  }

  /**
   * Load CAPICObject references
   *
   * @return void
   * @throws Exception
   */
  function loadRefs() {
    if (static::$load_lite) {
      return;
    }

    foreach (static::$refs as $_name => $_ref) {
      $this->{$_name} = $this->loadRef($_name);
    }
  }

  /**
   * Load CAPICObject references for API purposes
   *
   * @return void
   * @throws Exception
   */
  function loadAPIRefs() {
    $this->loadRefs();
  }

  /**
   * Load a CAPICObject reference
   *
   * @param string $name Reference name
   *
   * @return CAPICObject
   * @throws Exception
   */
  function loadRef($name) {
    $ref = static::$refs[$name];

    $class    = key($ref);
    $field_id = $ref[$class];

    if (!$this->{$field_id}) {
      return null;
    }

    if (preg_match('/^meta\|(?P<class>.*)$/', $class, $matches)) {
      $class = $this->{$matches['class']};
    }

    // Do not load CFile references (specific API call required)
    if ($class == 'CFile') {
      return null;
    }

    /** @var CStoredObject $object */
    $object = new $class();
    $object->load($this->{$field_id});

    /** @var CAPICObject $api_class */
    $api_class = static::getAPIClass($class);

    return $api_class::mbObjectToAPI($object);
  }

  /**
   * Map a CStoredObject into an API object
   *
   * @param CStoredObject $object Mediboard object
   *
   * @return CAPICObject
   * @throws Exception
   */
  static function mbObjectToAPI(CStoredObject $object) {
    $class = static::getAPIClass($object->_class);

    /** @var CAPICObject $api_object */
    $api_object = new $class();

    foreach ($api_object::$fields as $_object_field => $_api_field) {
      $api_object->{$_api_field} = $object->{$_object_field};
    }

    $api_object->loadAPIRefs();
    $api_object->updateFields();

    // Not needed here, or it will be printed or transmitted
    unset($api_object->_ref_object);

    return $api_object;
  }

  /**
   * Bind a CAPICObject from array
   *
   * @param array $params Array of field => value
   *
   * @return void
   */
  function bind(array &$params) {
    foreach ($params as $_field => $_param) {
      if ($_param === null) {
        continue;
      }

      $this->{$_field} = utf8_decode($_param);
    }
  }

  /**
   * Map an API object into a CStoredObject
   *
   * @return void
   * @throws CMbException
   */
  function APItoMbObject() {
    throw new CMbException('CAPI-error-API to MbObject not implemented');
  }

  /**
   * Store a CStoredObject from a CAPICObject
   *
   * @param array|null $params Array of field => value
   *
   * @throws Exception
   * @return void
   */
  function storeMbObject(array &$params = null) {
    if ($params) {
      $this->bind($params);
    }

    if (!$this->checkPermObject()) {
      throw new CAPINoPermissionException('common-error-No permission');
    }


    $this->_ref_object = $this->APItoMbObject();
    $this->_ref_object->updateFormFields();

    if ($msg = $this->_ref_object->store()) {
      throw new Exception($msg, 412);
    }

    $this->storeExtID(true);
  }

  /**
   * Stores SYNC external ID
   *
   * @param bool|true $check_creation Do we have to check if object has been created by sync third party?
   *
   * @return null|string
   * @throws Exception
   */
  function storeExtID($check_creation = true) {
    $object   = $this->_ref_object;

    if ($this->_tmp_id) {
      $tmp_id_tag = CIdSante400::getMatch($object->_class, static::TMP_TAG, $this->_tmp_id, $object->_id);

      if (!$tmp_id_tag || !$tmp_id_tag->_id) {
        $tmp_id_tag = new CIdSante400();
        $tmp_id_tag->setObject($object);

        $tmp_id_tag->tag             = static::TMP_TAG;
        $tmp_id_tag->id400           = $this->_tmp_id;
        $tmp_id_tag->datetime_create = CMbDT::dateTime();

        // Do not return here
        return $tmp_id_tag->store();
      }
    }
  }

  /**
   * Returns enabled object classes
   *
   * @param bool $strict Use API_VERSION
   *
   * @throws CMbException
   *
   * @return array
   */
  static function getObjClasses($strict = true) {
    throw new CMbException('CAPI-error-getObjectClasses method must be implemented');
  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getAllowedOfflineObjClasses() {
    throw new CMbException('CAPI-error-getAllowedOfflineObjClasses method must be implemented');
  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getCreateOfflineObjClasses() {
    throw new CMbException('CAPI-error-getAllowedOfflineObjClasses method must be implemented');
  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getUpdateOfflineObjClasses() {
    throw new CMbException('CAPI-error-getUpdateOfflineObjClasses method must be implemented');

  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getDeleteOfflineObjClasses() {
    throw new CMbException('CAPI-error-getDeleteOfflineObjClasses method must be implemented');
  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getCreateObjClasses() {
    throw new CMbException('CAPI-error-getCreateObjClasses method must be implemented');
  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getUpdateObjClasses() {
    throw new CMbException('CAPI-error-getUpdateObjClasses method must be implemented');
  }

  /**
   * @throws CMbException
   *
   * @return void
   */
  static function getDeleteObjClasses() {
    throw new CMbException('CAPI-error-getDeleteObjClasses method must be implemented');
  }

  /**
   * Returns enabled change types
   *
   * @return array
   */
  static function getChangeTypes() {
    $enabled_types = [
      'create',
      'update',
      'delete',
      'merge'
    ];

    return $enabled_types;
  }

  /**
   * Get all objects specs
   *
   * @throws CMbException
   *
   * @return array
   */
  static function getAllSpecs() {
    $specs = [];

    foreach (static::$classes as $_class) {
      $_api_class = static::getAPIClass($_class);

      /** @var CAPICObject $_api_object */
      $_api_object = new $_api_class();

      $specs[$_class] = $_api_object->getSpecs();
    }

    return $specs;
  }

  /**
   * Get all objects back_props
   *
   * @throws CMbException
   *
   * @return array
   */
  static function getAllBackProps() {
    $back_props = [];

    foreach (static::getObjClasses() as $_class) {

      $_api_class = static::getAPIClass($_class);
      /** @var CAPICObject $_api_object */
      $_api_object = new $_api_class();

      $back_props[$_class] = $_api_object->getBackProps();
    }

    return $back_props;
  }

  /**
   * Get object back props
   *
   * @return array
   */
  function getBackProps() {
    return static::$backrefs;
  }

  /**
   * Get object specs
   *
   * @throws CMbException
   *
   * @return array | null
   */
  function getSpecs() {
    $api_specs = [];

    $mb_object = $this->APItoMbObject();
    if (!$mb_object) {
      return null;
    }

    $mb_specs  = $mb_object->getSpecs();
    foreach (static::$fields as $_mb_field => $_api_field) {
      $api_specs[$_api_field] = null;

      $_spec = CMbArray::extract($mb_specs, $_mb_field);

      if ($_spec) {
        $api_specs[$_api_field] = $_spec->prop;
      }
    }

    $api_specs = array_merge($api_specs, $this->getAPISpecs());

    return $api_specs;
  }

  /**
   * Get API specific specs
   *
   * @return array
   */
  function getAPISpecs() {
    $specs = [];

    foreach (static::$refs as $_ref => $_field) {
      $_k = key($_field);
      $_v = reset($_field);

      $specs[$_ref] = "object class|{$_k}|{$_v}";
    }

    return $specs;
  }

  /**
   * Set the temporary object ID
   *
   * @param string $tmp_id Temporary ID
   *
   * @return mixed
   * @throws Exception
   */
  function setTemporaryID($tmp_id) {
    return $this->_tmp_id = $tmp_id;
  }

  /**
   * Check if object was already created (connectivity issues)
   *
   * @return CStoredObject|null
   * @throws Exception
   */
  function checkTemporaryID() {
    $object     = $this->APItoMbObject();
    $tmp_id_tag = CIdSante400::getMatch($object->_class, static::TMP_TAG, $this->_tmp_id);

    if ($tmp_id_tag && $tmp_id_tag->_id) {
      return $tmp_id_tag->loadTargetObject();
    }

    return null;
  }

  /**
   * Check given parameters that are mandatory (in order to determine owner's rights)
   *
   * @param array $params = []
   *
   * @return void
   */
  static function checkMandatoryParameters(&$params = []) {
  }

  /**
   * Check if a mandatory parameter is here
   *
   * @param string $needle   Parameter name
   * @param array  $haystack To search from
   *
   * @throws CAPIException
   * @return void
   */
  static function checkMandatoryParameter($needle, $haystack) {
    if (!$haystack || !isset($haystack[$needle]) || !$haystack[$needle]) {
      throw new CAPIException('common-error-Missing parameter: %s', 412, $needle);
    }
  }

  /**
   * Check if current user has permission on object with these given attributes
   *
   * @return bool
   */
  function checkPermObject() {
    return false;
  }
}
