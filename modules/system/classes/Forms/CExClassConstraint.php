<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Ox\Core\CAppUI;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMixItem;

/**
 * Form constraint
 *
 * Les contraintes d'évènement permettent de dire que le formulaire ne doit être propose que dans certaines conditions.
 * Chaque contrainte est évaluée, si l'une d'entre elles est vérifiée, le formulaire est proposé.
 */
class CExClassConstraint extends CMbObject implements FormComponentInterface {
  public $ex_class_constraint_id;

  //public $ex_class_id;
  public $ex_class_event_id;
  public $field;
  public $operator;
  public $value;
  public $quick_access;

  /** @var CExClassEvent */
  public $_ref_ex_class_event;

  /** @var CMbObject */
  public $_ref_target_object;

  /** @var CMbFieldSpec */
  public $_ref_target_spec;

  /** @var string */
  public $_quick_access_creation;
  
  static $_load_lite = false;

  /**
   * @inheritdoc
   */
  function getSpec() {

    $spec = parent::getSpec();
    $spec->table = "ex_class_constraint";
    $spec->key   = "ex_class_constraint_id";
    $spec->uniques["constraint"] = array("ex_class_event_id", "field", "value");
    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();
    $props["ex_class_event_id"] = "ref notNull class|CExClassEvent back|constraints";
    $props["field"]       = "str notNull";
    $props["operator"]    = "enum notNull list|=|!=|>|>=|<|<=|startsWith|endsWith|contains|in|notIn default|=";
    $props["value"]       = "str notNull";
    $props["quick_access"] = "bool notNull default|0";
    return $props;
  }

  /**
   * Get field and object corresponding to $this->field field
   *
   * @param CMbObject $object Object
   *
   * @return array(CMbObject,string)
   */
  function getFieldAndObject(CMbObject $object){
    return self::getFieldAndObjectStatic($object, $this->field);
  }

  /**
   * Get field and object corresponding to $field field
   *
   * @param CMbObject $object Object
   * @param string    $field  Field name
   *
   * @return array
   */
  static function getFieldAndObjectStatic(CMbObject $object, $field) {
    if (strpos($field, "CONNECTED_USER") === 0) {
      $object = CMediusers::get();

      if ($field != "CONNECTED_USER") {
        $field = substr($field, 15);
      }
    }

    return array($object, $field);
  }

  /**
   * Resolve spec from $this->field
   *
   * @param CModelObject $ref_object Ref object
   *
   * @return CMbFieldSpec|null
   */
  function resolveSpec(CModelObject $ref_object){
    /** @var CMbObject $ref_object */
    /** @var string $field */
    list($ref_object, $field) = $this->getFieldAndObject($ref_object);

    $parts = explode("-", $field);
    $connected_user = CExClassEvent::getConnectedUserSpec();

    if (count($parts) == 1) {
      if ($field == "CONNECTED_USER") {
        $spec = $connected_user;
      }
      else {
        $spec = $ref_object->_specs[$field];
      }
    }
    else {
      $subparts = explode(".", $parts[0]);

      /** @var CRefSpec $_spec */
      if ($subparts[0] == "CONNECTED_USER") {
        $_spec = $connected_user;
      }
      else {
        $_spec = $ref_object->_specs[$subparts[0]];
      }

      if (count($subparts) > 1) {
        $class = $subparts[1];
      }
      else {
        if (!$_spec->class) {
          return null;
        }

        $class = $_spec->class;
      }

      $obj = new $class;

      if ($parts[1] == "CONNECTED_USER") {
        $spec = $connected_user;
      }
      else {
        $spec = $obj->_specs[$parts[1]];
      }
    }

    return $spec;
  }

  /**
   * Resolve an object from an object and a formard ref path
   *
   * @param CMbObject $object The object to resolve forward ref object
   * @param string    $field  The path to resolve
   *
   * @return array|null
   */
  static function resolveObjectFieldStatic(CMbObject $object, $field) {
    $parts = explode("-", $field);

    if (count($parts) == 1) {
      return array(
        "object" => $object,
        "field"  => $parts[0],
      );
    }
    else {
      $subparts = explode(".", $parts[0]);
      $_field = $subparts[0];

      /** @var CRefSpec $_spec */
      $_spec = $object->_specs[$_field];

      if (count($subparts) <= 1 && !$_spec->class) {
        return null;
      }

      return array(
        "object" => $object->loadFwdRef($_field, true),
        "field"  => $parts[1],
      );
    }
  }

  /**
   * Resolve ovject field from $this->field
   *
   * @param CMbObject $object Object
   *
   * @return array(CMBobject,string)
   */
  function resolveObjectField(CMbObject $object){
    list($object, $field) = $this->getFieldAndObject($object);

    return self::resolveObjectFieldStatic($object, $field);
  }

  /**
   * Load target object
   *
   * @return CMbObject
   */
  function loadTargetObject(){
    $this->loadRefExClassEvent();
    $this->completeField("field", "value");

    if (!$this->_id) {
      return $this->_ref_target_object = new CMbObject;
    }

    $ref_object = new $this->_ref_ex_class_event->host_class;

    $spec = $this->resolveSpec($ref_object);

    if ($spec instanceof CRefSpec && $this->value && preg_match("/[a-z][a-z0-9_]+-[0-9]+/i", $this->value)) {
      $this->_ref_target_object = CMbObject::loadFromGuid($this->value);
    }
    else {
      // empty object
      $this->_ref_target_object = new CMbObject;
    }

    $this->_ref_target_spec = $spec;

    return $this->_ref_target_object;
  }

  /**
   * @inheritdoc
   */
  function updateFormFields(){
    parent::updateFormFields();
    
    if (self::$_load_lite) {
      return;
    }

    $this->loadRefExClassEvent(true);

    $host_class = $this->_ref_ex_class_event->host_class;

    if (!class_exists($host_class)) {
      return;
    }

    /** @var CMbObject $object */
    /** @var string $field */
    list($object, $field) = $this->getFieldAndObject(new $host_class);
    $host_class = $object->_class;

    $parts = explode("-", $field);
    $subparts = explode(".", $parts[0]);

    if (count($subparts) > 1) {
      // first part
      $this->_view = CAppUI::tr("$host_class-{$subparts[0]}")." de type ".CAppUI::tr("{$subparts[1]}");
    }
    else {
      // second part
      if (count($parts) > 1) {
        $this->_view = CAppUI::tr("$host_class-{$parts[0]}");
      }
      else {
        $this->_view = CAppUI::tr("$host_class-{$field}");
      }
    }

    // 2 levels
    if (count($parts) > 1) {
      if (isset($subparts[1])) {
        $class = $subparts[1];
      }
      else {
        /** @var CRefSpec $_spec */
        $_spec = $object->_specs[$subparts[0]];
        $class = $_spec->class;
      }

      /*if ($_spec instanceof CRefSpec) {
        $class =
      }*/

      $this->_view .= " / ".CAppUI::tr("{$class}-{$parts[1]}");
    }
  }

  /**
   * Check constraint
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  function checkConstraint(CMbObject $object) {
    $this->completeField("field", "value");

    $this->loadObjectRefs($object);

    $object_field = $this->resolveObjectField($object);

    if (!$object_field) {
      return false;
    }

    /** @var CMbObject $object_fwd */
    $object_fwd = $object_field["object"];
    $field      = $object_field["field"];

    // cas ou l'objet retrouvé n'a pas le champ (meta objet avec classe differente)
    if (!isset($object_fwd->_specs[$field]) && $field != "CONNECTED_USER") {
      return false;
    }

    $this->loadObjectRefs($object_fwd);

    if ($field == "CONNECTED_USER") {
      $value = $object_fwd->_guid;
    }
    else {
      $value = $object_fwd->$field;

      if ($object_fwd->_specs[$field] instanceof CRefSpec) {
        $_obj = $object_fwd->loadFwdRef($field, true);
        $value = $_obj->_guid;
      }
    }

    $value_comp = $this->value;
    if ($this->operator == "in" || $this->operator == 'notIn') {
      $value_comp = $this->getInValues();
    }

    return CExClass::compareValues($value, $this->operator, $value_comp);
  }

  /**
   * Load object useful references
   * TODO Namespace when adding externals
   *
   * @param CMbObject $object Object to load the references of
   *
   * @return void
   */
  function loadObjectRefs(CMbObject $object) {
    if (isset($object->_object_refs_loaded)) {
      return;
    }

    if (!$object instanceof CAdministration
        && !$object instanceof CPrescriptionLine
        && !$object instanceof CPrescription
    ) {
      $object->loadView();
    }

    if ($object instanceof CPrescriptionLineMedicament) {
      $object->isHorsT2A();
      $object->loadClasseATC();
      $object->isHorsLivret();
    }

    if ($object instanceof CPrescriptionLineMixItem) {
      $object->isHorsLivret();
    }
    
    $object->_object_refs_loaded = true;
  }

  /**
   * Get values for the "in" operator
   *
   * @return string[]
   */
  function getInValues(){
    return array_map("trim", preg_split("/[\r\n]+/", $this->value));
  }

  /**
   * Load class event object
   *
   * @param bool $cache Use object cache
   *
   * @return CExClassEvent
   */
  function loadRefExClassEvent($cache = false){
    return $this->_ref_ex_class_event = $this->loadFwdRef("ex_class_event_id", $cache);
  }
}
