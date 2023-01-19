<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Domain handler
 */
class CEAIGroupsHandler extends ObjectHandler {

  static $handled = array("CGroups");
  public $create = false;

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    return in_array($object->_class, self::$handled);
  }



  /**
   * @inheritdoc
   */
  function onBeforeStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if (!$object->_id) {
      $this->create = true;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if (!$object->_id || !$this->create) {
      return false;
    }

    $group_id = $object->_id;
    $object_class = array("CSejour", "CPatient");
    global $dPconfig;
    $original_value = $dPconfig["eai"]["use_domain"];
    $dPconfig["eai"]["use_domain"] = "0";

    foreach ($object_class as $_class) {
      switch ($_class) {
        case "CSejour":
          $tag_group = CSejour::getTagNDA($group_id);
          break;
        case "CPatient":
          $tag_group = CPatient::getTagIPP($group_id);
          break;
        default:
          $tag_group = null;
      }

      if (!$tag_group) {
        continue;
      }

      $domain = new CDomain();
      $domain->tag = $tag_group;
      if ($domain->store()) {
        continue;
      }

      $group_domain               = new CGroupDomain();
      $group_domain->group_id     = $group_id;
      $group_domain->domain_id    = $domain->_id;
      $group_domain->object_class = $_class;
      $group_domain->master       = "1";
      $group_domain->store();
    }

    $dPconfig["eai"]["use_domain"] = "$original_value";

    return true;
  }
}
