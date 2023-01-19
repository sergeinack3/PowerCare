<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CObjectNavigation;

CCanDo::checkAdmin();
$object_class = CView::get("class_name", "str notNull");
$object_id   = CView::get("class_id", "str notNull");

CView::checkin();

$obj_nav = new CObjectNavigation($object_class, $object_id);

if (!$obj_nav->object_select) {
  CAppUI::stepAjax("Pas d'objet instancié.", UI_MSG_ERROR);
}

$grouped_fields       = $obj_nav->sortFields();

$obj_nav->object_select->loadAllFwdRefs();
$obj_nav->object_select->countAllBackRefs();

$counts = array(
  "total" => 0,
  "form"  => 0,
  "plain" => 0
);
foreach ($obj_nav->object_select->_count as $_back => $_count) {
  if ($_count > 0) {
    $counts["total"] += $_count;
    $obj_nav->object_select->loadBackRefs($_back, null, 50);
  }
}

$counts["plain"] = 0;
$counts["form"] = 0;
foreach ($grouped_fields as $fieldset => $fields){
    $counts["plain"] += count($fields["plain"]["fields"]) + count($fields["plain"]["refs"]);
    $counts["form"] += count($fields["form"]["fields"]) + count($fields["form"]["refs"]);
    if (!$fields["form"]["refs"]) {
        $counts["form"] += count($fields["form"]["fields"]);
    }
}

// Envoie des variables smarty
$smarty = new CSmartyDP();
$smarty->assign("object_select", $obj_nav->object_select);
$smarty->assign("grouped_fields", $grouped_fields);
$smarty->assign("counts", $counts);
$smarty->assign("count_obj", $obj_nav->object_select->_count);
$smarty->assign("start", 0);
$smarty->display("vw_class_details.tpl");
