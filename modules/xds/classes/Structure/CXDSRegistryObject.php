<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds\Structure;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CClassMap;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Classe mère de RegistryPackage, ExtrinsicObject, externalIdentifier, Association, classification
 */
class CXDSRegistryObject {
  public $id;
  public $_class;
  public $objectType;
  public $versionInfo;

  public $_group_id;

  // Meta
  public $object_id;
  public $object_class;
  public $_ref_object;

  /**
   * @see parent::getProps
   */
  function getProps() {
    $props["_group_id"]    = "ref notNull class|CGroups";
    $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class";
    $props["object_class"] = "str notNull class show|0";

    return $props;
  }

  /**
   * Création d'une instance de la classe
   *
   * @param String $id String
   */
  function __construct($id) {
    $this->_class    = CClassMap::getSN($this);
    $this->id        = $id;
    $this->_group_id = CGroups::loadCurrent()->_id;
  }

  /**
   * Setter generic
   *
   * @param String   $name  String
   * @param String[] $value String[]
   *
   * @return void
   */
  function setSlot($name, $value) {
    $this->$name = new CXDSSlot($name, $value);
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
   * @param bool $cache
   * @deprecated
   * @todo redefine meta raf
   * @return mixed
   * @throws Exception
   */
  public function loadTargetObject($cache = true) {
    return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
  }

  /**
   * @inheritDoc
   * @todo remove
   */
  function loadRefsFwd() {
    $this->loadTargetObject();
  }
}
