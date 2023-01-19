<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation_id = CView::get("operation_id", "num");
$date         = CView::get("date", "date");
$chir_id      = CView::get("chir_id", "num");
$plageop_id   = CView::get("plageop_id", "num");
$salle_id     = CView::get("salle_id", "num");

CView::checkin();

$where = array(
  "plagesop.date"       => "= '$date'",
  "plagesop.chir_id"    => "= '$chir_id'"
);

// Modification de plage dans une salle
if ($salle_id) {
  $where["plagesop.salle_id"] = "= '$salle_id'";
}
// Choix d'une plage de destination pour un déplacement d'intervention
else {
  $op = new COperation();
  $op->load($operation_id);

  CAccessMedicalData::logAccess($op);

  $op->loadRefPlageOp();
  $where["plagesop.plageop_id"] = "!= '" . $op->_ref_plageop->_id . "'";
}

$plage = new CPlageOp();
$plages = $plage->loadList($where);

CStoredObject::massLoadFwdRef($plages, "salle_id");

/** @var CPlageOp $_plage */
foreach ($plages as $_plage) {
  $_plage->loadRefSalle();
}

$smarty = new CSmartyDP();

$smarty->assign("operation_id", $operation_id);
$smarty->assign("salle_id"    , $salle_id);
$smarty->assign("plages"      , $plages);

$smarty->display("inc_choose_plage.tpl");