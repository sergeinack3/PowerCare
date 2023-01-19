<?php
/**
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Printing;

use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CFunctions;

/**
 * Lien entre une imprimante réseau et une fonction
 */
class CPrinter extends CMbObject {
  // DB Table key
  public $printer_id;

  // DB Fields
  public $function_id;
  public $label;

  public $object_class;
  public $object_id;
  public $_ref_object;

  // Ref fields
  public $_ref_function;
  public $_ref_source;

  /**
   * @see parent::getSpec
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = 'printer';
    $spec->key   = 'printer_id';

    return $spec;
  }

  /**
   * @see parent::getProps
   */
  function getProps() {
    $props                 = parent::getProps();
    $props["function_id"]  = "ref class|CFunctions notNull back|printers";
    $props["object_id"]    = "ref notNull class|CSourcePrinter meta|object_class back|printers";
    $props["object_class"] = "str notNull class show|0";
    $props["label"]        = "str";

    return $props;
  }

  /**
   * @deprecated
   * @see parent::loadTargetObject
   */
  function loadTargetObject($cache = true) {
    $target = CMbMetaObjectPolyfill::loadTargetObject($this, $cache);

    /** @var $object CSourcePrinter */
    $this->_view = $target->_view;

    return $target;
  }

  /**
   * Charge la fonction associée à l'imprimante
   *
   * @param bool $cached Load from cache
   *
   * @return CFunctions
   */
  function loadRefFunction($cached = true) {
    return $this->_ref_function = $this->loadFwdRef("function_id", $cached);
  }

  /**
   * Charge la source d'impression
   *
   * @return CSourcePrinter
   */
  function loadRefSource() {
    $source_guid = $this->object_class . '-' . $this->object_id;

    return $this->_ref_source = CMbObject::loadFromGuid($source_guid);
  }


  /**
   * @param CStoredObject $object
   * @deprecated
   * @todo redefine meta raf
   * @return void
   */
  public function setObject(CStoredObject $object) {
    CMbMetaObjectPolyfill::setObject($this, $object);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    parent::loadRefsFwd();
    $this->loadTargetObject();
  }
}
