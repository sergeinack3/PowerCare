<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;

/**
 * Link a Interop Sender to an object
 */
class CObjectToInteropSender extends CMbObject {

  /**
   * Table Key
   *
   * @var integer
   */
  public $object_to_interop_sender_id;

  /**
   * The type of the InteropSender
   *
   * @var string
   */
  public $sender_class;

  /**
   * The id of the interop sender
   *
   * @var integer
   */
  public $sender_id;

  /**
   * The object class
   *
   * @var string
   */
  public $object_class;

  /**
   * The object id
   *
   * @var integer
   */
  public $object_id;

  /**
   * The sender
   *
   * @var CInteropSender
   */
  public $_ref_sender;

  /**
   * The object
   *
   * @var CMbObject
   */
  public $_ref_object;

  /**
   * Initialize the class specifications
   *
   * @return CMbFieldSpec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "object_to_interop_sender";
    $spec->key   = "object_to_interop_sender_id";
    $spec->uniques['linked_object'] = array('sender_id', 'object_id');
    return $spec;
  }

  /**
   * Get the properties of our class as string
   *
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["sender_class"] = "str notNull";
    $props["sender_id"]    = "ref notNull class|CInteropSender meta|sender_class back|object_links";
    $props["object_class"] = "str notNull";
    $props["object_id"]    = "ref notNull class|CMbObject meta|object_class back|interop_sender_objects";
    return $props;
  }

  /**
   * Update the form fields
   *
   * @return void
   */
  function updateFormFields() {
    parent::updateFormFields();
    $this->_view = $this->loadRefObject()->_view;
  }

  /**
   * Load the object
   *
   * @param bool $cached Use object cache when possible
   *
   * @return CMbObject
   */
  function loadRefObject($cached = true) {
    return $this->_ref_object = $this->loadFwdRef("object_id", $cached);
  }

  /**
   * Load sender
   *
   * @param bool $cached Use object cache when possible
   *
   * @return CInteropSender
   */
  function loadRefSender($cached = true) {
    return $this->_ref_sender = $this->loadFwdRef("sender_id", $cached);
  }

  /**
   * Load all the objects linked to the sender
   *
   * @return CStoredObject[]
   */
  function loadAllObjects() {
    $where = array("sender_id" => " = '$this->sender_id'");
    return $this->loadList($where);
  }

  /**
   * Load all the objects linked to the given sender
   *
   * @param string $sender_id integer The id of the sender
   *
   * @return CStoredObject[]
   */
  static function loadAllObjectsFor($sender_id) {
    $where = array("sender_id" => " = '$sender_id'");
    $tmp = new CObjectToInteropSender;
    return $tmp->loadList($where);
  }
}