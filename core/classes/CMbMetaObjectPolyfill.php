<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Class CMbMetaObjectPolyfill
 */
abstract class CMbMetaObjectPolyfill {

  /**
   * @param CStoredObject $object
   * @param CStoredObject $target
   *
   * @return CStoredObject
   */
  public static function setObject(CStoredObject $object, CStoredObject $target): CStoredObject {
    $object->_ref_object  = $target;
    $object->object_id    = $target->_id;
    $object->object_class = $target->_class;

    return $object;
  }

  /**
   *
   * @param CStoredObject $object
   * @param bool          $cache
   *
   * @return bool|CStoredObject|CExObject|null
   * @throws Exception
   */
  public static function loadTargetObject(CStoredObject $object, bool $cache = true) {
    if ($object->_ref_object || !$object->object_class) {
      return $object->_ref_object;
    }

    if (!class_exists($object->object_class)) {
      $ex_object = CExObject::getValidObject($object->object_class);

      if (!$ex_object) {
        CModelObject::error("Unable-to-create-instance-of-object_class%s-class", $object->object_class);

        return null;
      }
      else {
        $ex_object->load($object->object_id);
        $object->_ref_object = $ex_object;
      }
    }
    else {
      $object->_ref_object = $object->loadFwdRef("object_id", $cache);
    }

    if (!$object->_ref_object->_id) {
      $object->_ref_object->load(null);
      $object->_ref_object->_view = "Element supprimé";
    }

    return $object->_ref_object;
  }
}