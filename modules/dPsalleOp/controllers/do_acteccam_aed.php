<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Acte CCAM controller
 */
class CDoActeCCAMAddEdit extends CDoObjectAddEdit {
  /** @var CMbObject */
  public $_ref_object;

  /** @var array A list of the object fields */
  public $object_fields = array();

  /** @var int The number of objects to store for the multiple store */
  public $objects_count;

  /** @var int The index for the current object's data */
  public $current_index;

  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct("CActeCCAM", "acte_id");
  }

  /**
   * @inheritdoc
   */
  public function doIt() {
    /* Store multiple acts with different data */
    if (CMbArray::extract($this->request, 'multiple')) {
      $this->doItMultiple();

      $this->doRedirect();
    }
    else {
      parent::doIt();
    }
  }

  /**
   * Do the action for multiple objects
   *
   * @return void
   */
  public function doItMultiple() {
    $this->getObjectFields();
    $this->setRequestParameters();
    $this->objects_count = CMbArray::extract($this->request, 'objects_count');
    $delete = CMbArray::extract($this->request, 'del');

    for ($this->current_index = 0; $this->current_index < $this->objects_count; $this->current_index++) {
      $this->doBindMultiple();

      if ($delete) {
        $this->doDelete();
      }
      else {
        $this->doStore();
      }
    }
  }

  /**
   * @inheritdoc
   */
  function doBind($reinstanciate_objects = false) {
    parent::doBind($reinstanciate_objects);
    $this->bindModifiers($this->request);
  }

  /**
   * Bind the request data to an object in case of multiple objects
   *
   * @return void
   */
  public function doBindMultiple() {
    $this->_obj = new $this->className();
    $this->_old = new $this->className();
    $this->onAfterInstanciation();

    $this->_obj->bind($this->getDataForCurrentObject());
    $this->_old->load($this->_obj->_id);
  }

  /**
   * Set the request's parameters (ajax, callback, redirection, ...)
   *
   * @return void
   */
  protected function setRequestParameters() {
    $this->ajax            = CMbArray::extract($this->request, "ajax");
    $this->suppressHeaders = CMbArray::extract($this->request, "suppressHeaders");
    $this->callBack        = CMbArray::extract($this->request, "callback");
    $this->postRedirect    = CMbArray::extract($this->request, "postRedirect");

    if ($this->postRedirect) {
      $this->redirect = $this->postRedirect;
    }
  }

  /**
   * Get the object fields
   * 
   * @return void
   */
  protected function getObjectFields() {
    $object = new $this->className();
    $spec = $object->getSpec();
    $this->object_fields = array_merge(array($spec->key), array_keys($object->getProps()));
  }

  /**
   * Extract the data for the current index from the request data
   *
   * @return array
   */
  protected function getDataForCurrentObject() {
    $data = array();

    foreach ($this->request as $field => $value) {
      if (!in_array($field, $this->object_fields)) {
        continue;
      }

      if (!is_array($value)) {
        $data[$field] = $value;
      }
      elseif (array_key_exists($this->current_index, $value)) {
        $data[$field] = $value[$this->current_index];
      }
    }

    return $data;
  }

  /**
   * Bind the modifiers data to the current object
   *
   * @param array $data The data containing the modifiers (ie the request data)
   *
   * @return void
   */
  protected function bindModifiers($data = array()) {
    if ($this->_obj->_edit_modificateurs) {
      $this->_obj->modificateurs = "";
      foreach ($data as $propName => $propValue) {
        $matches = null;
        if (preg_match("/modificateur_(.)(.)(.)?/", $propName, $matches)) {
          $modificateur = $matches[1];
          if (strpos($this->_obj->modificateurs, $matches[1]) === false) {
            $this->_obj->modificateurs .= $modificateur;
            if (isset($matches[3]) && $matches[3] == 2) {
              $this->_obj->modificateurs .= $matches[2];
            }
          }
        }
      }
    }

    $this->_obj->loadRefObject();
    $this->_ref_object = $this->_obj->_ref_object;
  }
}

$do = new CDoActeCCAMAddEdit();
$do->doIt();
