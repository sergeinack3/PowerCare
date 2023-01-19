<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$chir         = CView::get("chir", "ref class|CMediusers");
$date         = CView::get("date_plagesel", "date default|now", true);
$curr_op_time = CView::get("curr_op_time" , "time default|25:00");
$multiple     = CView::get("multiple", "bool default|0");

CView::checkin();

$resp_bloc = CModule::getInstalled("dPbloc")->getPerm(PERM_EDIT);

// Chargement du chirurgien
$mediChir = CMediusers::get($chir);
$mediChir->loadBackRefs("secondary_functions");
$secondary_functions = array();
foreach ($mediChir->_back["secondary_functions"] as $curr_sec_func) {
  $secondary_functions[] = $curr_sec_func->function_id;
}

// Chargement de la liste des blocs opératoires
$bloc = new CBlocOperatoire();
$blocs = $bloc->loadGroupList(["actif" => "= '1'"], "nom");
CStoredObject::massLoadBackRefs($blocs, "salles", "nom", ["actif" => "= '1'"]);
foreach ($blocs as $_bloc) {
  $_bloc->loadRefsSalles(["actif" => "= '1'"]);
  $_bloc->_date_min = CMbDT::date("+ " . $_bloc->days_locked . " DAYS");
}

$ds = CSQLDataSource::get("std");

// Chargement des plages pour le chir ou sa spécialité par bloc
$where = array();
$selectPlages  = "(plagesop.chir_id = %1 OR plagesop.spec_id = %2 OR plagesop.spec_id ".CSQLDataSource::prepareIn($secondary_functions).") OR (plagesop.chir_id IS NULL AND plagesop.spec_id IS NULL AND plagesop.urgence = '1')";
$where[]       = $ds->prepare($selectPlages, $mediChir->user_id, $mediChir->function_id);
$month_min = CMbDT::transform("+ 0 month", $date, "%Y-%m-00");
$month_max = CMbDT::transform("+ 1 month", $date, "%Y-%m-00");
$where["date"] = "BETWEEN '$month_min' AND '$month_max'";
if (!$resp_bloc) {
  $where[] = "date >= '".CMbDT::date()."'";
}
$order = "date, debut";
$plage = new CPlageOp();
$listPlages = array();
foreach ($blocs as $_bloc) {
  $where["salle_id"] = CSQLDataSource::prepareIn(array_keys($_bloc->_ref_salles));
  $listPlages[$_bloc->_id] = $plage->loadList($where, $order);
  if (!count($listPlages[$_bloc->_id])) {
    unset($listPlages[$_bloc->_id]);
  }
}

$time = explode(":", $curr_op_time);
$nb_secondes = $time[0]*3600 + $time[1]*60;

$_plage = new CPlageOp();
foreach ($listPlages as $_bloc) {
  CStoredObject::massLoadFwdRef($_bloc, "salle_id");
  CStoredObject::massLoadBackRefs($_bloc, "notes");
  foreach ($_bloc as $_plage) {
    /* @var CPlageOp $_plage */
    $_plage->loadRefSalle();
    $_plage->multicountOperations($nb_secondes);
    $_plage->loadRefsNotes();
    $_plage->loadRefSpec(1);
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("blocs"     , $blocs);
$smarty->assign("listPlages", $listPlages);
$smarty->assign("resp_bloc" , $resp_bloc);
$smarty->assign("multiple"  , $multiple);

$smarty->display("inc_list_plages");
