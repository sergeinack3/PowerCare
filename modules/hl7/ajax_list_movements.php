<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CMovement;

/**
 * Movements
 */
CCanDo::checkRead();

$page          = CView::get("page", "num default|0");
$movement_type = CView::get("movement_type", "str");
$sejour_id     = CView::get("object_id", "ref class|CSejour");
$spec_date_min = array(
  "dateTime",
  "default" => CMbDT::dateTime("-7 day")
);
$_date_min     = CView::get("_date_min", $spec_date_min);
$spec_date_max = array(
  "dateTime",
  "default" => CMbDT::dateTime("+1 day")
);
$_date_max     = CView::get("_date_max", $spec_date_max);

CView::checkin();

$movement = new CMovement();

$where = array();
if ($sejour_id) {
  $where["sejour_id"] = " = '$sejour_id'";
}
if ($movement_type) {
  $where["movement_type"] = " = '$movement_type'";
}
if ($_date_min && $_date_max) {
  $where['start_of_movement'] = " BETWEEN '" . $_date_min . "' AND '" . $_date_max . "' ";
}

$forceindex[]    = "start_of_movement";
$total_movements = $movement->countList($where, null, null, $forceindex);

// Requête du filtre
$step  = 25;
$order = "last_update DESC, start_of_movement DESC";
/** @var CMovement[] $movements */
$movements = $movement->loadList($where, $order, "$page, $step", null, null, $forceindex);
$sejours = CStoredObject::massLoadFwdRef($movements, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

CStoredObject::massLoadFwdRef($movements, "affectation_id");
foreach ($movements as $_movement) {
  $_movement->loadRefSejour()->loadRefPatient();
  $_movement->loadRefAffectation();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("movements"      , $movements);
$smarty->assign("total_movements", $total_movements);
$smarty->assign("movement"       , $movement);
$smarty->assign("movement_type"  , $movement_type);
$smarty->assign("page"           , $page);
$smarty->display("inc_list_movements.tpl");