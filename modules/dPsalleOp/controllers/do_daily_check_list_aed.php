<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;
use Ox\Mediboard\SalleOp\CDailyCheckList;

$object_class        = CView::post("object_class", 'str');
$object_id           = CView::post("object_id", 'ref meta|object_class');
$type                = CView::post("type", 'str');
$daily_check_list_id = CView::post("daily_check_list_id", 'ref class|CDailyCheckList');

CView::checkin();

// On recherche une check list déja remplie, pour éviter les doublons
if (!$daily_check_list_id && in_array($object_class, CDailyCheckList::$_HAS_classes)) {
  /** @var COperation|CPoseDispositifVasculaire $object */
  $object = new $object_class;
  $object->load($object_id);

  $list = CDailyCheckList::getList($object, null, $type);

  if ($list->_id) {
    $_POST["daily_check_list_id"] = $list->_id;
  }
}

$do = new CDoObjectAddEdit("CDailyCheckList");
$do->doIt();
