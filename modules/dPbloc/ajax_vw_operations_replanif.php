<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;

CCanDo::checkRead();

$date_replanif = CView::get("date_replanif", "date", true);

CView::checkin();

$plage = new CPlageOp();

$where = array();
$where["date"] = "= '$date_replanif'";
/** @var CPlageOp[] $plages */
$plages = $plage->loadList($where);

$salles = CStoredObject::massLoadFwdRef($plages, "salle_id");
CStoredObject::massLoadFwdRef($plages, "chir_id");
CStoredObject::massLoadBackRefs($plages, "operations", "rank, time_operation, rank_voulu, horaire_voulu");

$plages_by_salle = array();

foreach ($plages as $key => $_plage) {
  $salle = $_plage->loadRefSalle();

  if (!count($salle->loadRefsBlocages($date_replanif))) {
    unset($plages[$key]);
    continue;
  }

  $operations = $_plage->loadRefsOperations();

  $sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
  CStoredObject::massLoadFwdRef($sejours, "patient_id");

  foreach ($operations as $_operation) {
    $_operation->loadRefPatient();
  }

  $_plage->loadRefChir();
  if (!isset($plages_by_salle[$salle->_id])) {
    $plages_by_salle[$salle->_id] = array();
  }
  $plages_by_salle[$salle->_id][] = $_plage;
}

$smarty = new CSmartyDP();

$smarty->assign("plages_by_salle"     , $plages_by_salle);
$smarty->assign("salles"              , $salles);
$smarty->assign("date_replanif"       , $date_replanif);
$smarty->assign("date_replanif_before", CMbDT::date("-1 day", $date_replanif));
$smarty->assign("date_replanif_after" , CMbDT::date("+1 day", $date_replanif));

$smarty->display("inc_vw_operations_replanif.tpl");
