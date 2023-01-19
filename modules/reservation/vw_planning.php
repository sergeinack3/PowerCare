<?php
/**
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

global $m, $current_m;

if (!isset($current_m)) {
  $current_m = CValue::get("current_m", $m);
}

$date_planning   = CValue::getOrSession("date_planning", CMbDT::date());
$praticien_id    = CValue::getOrSession("planning_chir_id");
$bloc_id         = CValue::getOrSession("bloc_id", "");
$show_cancelled  = CValue::getOrSession("show_cancelled", 0);
$show_operations = CValue::getOrSession("show_operations", 1);

$praticiens = new CMediusers;
$praticiens = $praticiens->loadPraticiens();
CMbObject::massLoadFwdRef($praticiens, "function_id");

foreach ($praticiens as $_prat) {
  $_prat->loadRefFunction();
}

$plageOp = new CPlageOp();
$plageOp->canDo();

$where               = array();
$whereSalle          = array();
$where["actif"]      = " = '1'";
$whereSalle["actif"] = " = '1'";
$bloc                = new CBlocOperatoire();
$blocs               = $bloc->loadGroupList(array("actif" => "= '1'"), null, null, null, null, $whereSalle);

$limit_date        = null;
$days_limit_future = abs(CAppUI::pref("planning_resa_days_limit"));
if ($days_limit_future != 0) {
  $limit_date = CMbDT::date("+ $days_limit_future DAYS", CMbDT::date());
}

$limit_past_date = null;
$days_limit_past = abs(CAppUI::pref("planning_resa_past_days_limit"));
if ($days_limit_past != 0) {
  $limit_past_date = CMbDT::date("- $days_limit_past DAYS", CMbDT::date());
}

$smarty = new CSmartyDP("modules/reservation");
$smarty->assign("current_m", $current_m);
$smarty->assign("date_planning", $date_planning);
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("blocs", $blocs);
$smarty->assign("plageop", $plageOp);
$smarty->assign("bloc_id", $bloc_id);
$smarty->assign("show_cancelled", $show_cancelled);
$smarty->assign("show_operations", $show_operations);
$smarty->assign("limit_date", $limit_date);
$smarty->assign("limit_past_date", $limit_past_date);
$smarty->display("vw_planning");
