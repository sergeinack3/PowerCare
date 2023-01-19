<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();
$ds = CSQLDataSource::get("std");

$date             = CValue::getOrSession("date", CMbDT::date());
$canceled         = CValue::getOrSession("canceled", 0);
$sans_anesth      = CValue::getOrSession("sans_anesth", 0);
$refresh          = CValue::get('refresh', 0);
$only_list_anesth = CValue::get('only_list_anesth', 0);
$sejour_ambu      = CValue::get('sejour_ambu', 1);
$sejour_comp      = CValue::get('sejour_comp', 1);
$hors_plage       = CValue::get('hors_plage', 1);

$nextmonth = CMbDT::date("first day of next month"   , $date);
$lastmonth = CMbDT::date("first day of previous month", $date);

// Sélection du praticien
$mediuser = CMediusers::get();
$listPrat = $mediuser->loadPraticiens(PERM_EDIT);
foreach ($listPrat as $_prat) {
  $_prat->loadRefFunction();
}

$selPrat = CValue::getOrSession("selPrat", $mediuser->isPraticien() ? $mediuser->user_id : null);

$all_prats = 0;
$selPraticien = new CMediusers();
if ($selPrat != 'all') {
  $selPraticien->load($selPrat);
}
else {
  $all_prats = 1;
  $user = CMediusers::get();
  $in_prats = CSQLDataSource::prepareIn(array_keys($listPrat));
}

$group = CGroups::loadCurrent();

if ($selPraticien->isAnesth() || $all_prats) {
  // Selection des différentes interventions de la journée par service
  $order = "operations.chir_id, operations.time_operation";
  $ljoin = array(
    "plagesop"    => "plagesop.plageop_id = operations.plageop_id",
    "sejour"      => "sejour.sejour_id = operations.sejour_id",
    "affectation" => "affectation.sejour_id = sejour.sejour_id
      AND '$date' BETWEEN DATE(affectation.entree)
      AND DATE(affectation.sortie)",
    "lit"         => "lit.lit_id = affectation.lit_id",
    "chambre"     => "chambre.chambre_id = lit.chambre_id",
    "service"     => "service.service_id = chambre.service_id"
  );
  if (!$all_prats) {
    $where_anesth = "operations.anesth_id = '$selPraticien->_id' OR plagesop.anesth_id = '$selPraticien->_id'";
  }
  else {
    $where_anesth = "operations.anesth_id $in_prats OR plagesop.anesth_id $in_prats";
  }

  if ($sans_anesth) {
    $where_anesth .= " OR (operations.anesth_id IS NULL AND plagesop.anesth_id IS NULL)";
  }

  $where = array(
    "operations.date" => "= '$date'",
    "sejour.group_id" => "= '$group->_id'",
  );
  $where[] = $where_anesth;

  $list_types = array();
  if ($sejour_comp) {
    $list_types[] = "comp";
  }
  if ($sejour_ambu) {
    $list_types[] = "ambu";
  }
  $where_type = "";
  if (count($list_types)) {
    $where_type .= "sejour.type ".CSQLDataSource::prepareIn(array_values($list_types));
  }
  if ($hors_plage) {
    if ($where_type != "") {$where_type .= " OR ";}
    $where_type .= "plagesop.plageop_id IS NULL";
  }
  $where[] = $where_type;

  if (!$canceled) {
    $where["operations.annulee"] = " = '0'";
  }

  /** @var COperation[] $allInterv */
  $allInterv = array();
  $allIntervByService = array();

  $service = new CService();
  $services = $service->loadGroupList();
  $interv = new COperation();
  foreach ($services as $_service) {
    $listInterv[$_service->_id] = array();
    if (count($list_types) || $hors_plage) {
      $where["service.service_id"]  = "= '$_service->_id'";
      $listInterv[$_service->_id]   = $interv->loadList($where , $order, null, "operations.operation_id", $ljoin);
    }

    $allInterv = array_merge($allInterv, $listInterv[$_service->_id]);

    if (count($listInterv[$_service->_id])) {
      $allIntervByService[$_service->_id] = array();
      $allIntervByService[$_service->_id] = array_merge($allIntervByService[$_service->_id], $listInterv[$_service->_id]);
    }
  }

  $listInterv["non_place"] = array();
  if (count($list_types) || $hors_plage) {
    $where["service.service_id"]   = "IS NULL";
    $listInterv["non_place"]   = $interv->loadList($where , $order, null, "operations.operation_id", $ljoin);
  }

  $allInterv = array_merge($allInterv, $listInterv["non_place"]);

  if (count($listInterv["non_place"])) {
    $allIntervByService["non_place"] = array();
    $allIntervByService["non_place"] = array_merge($allIntervByService["non_place"], $listInterv["non_place"]);
  }

  // Complétion du chargement
  $chirs     = CStoredObject::massLoadFwdRef($allInterv, "chir_id");
  $functions = CStoredObject::massLoadFwdRef($chirs, "function_id");
  $plages    = CStoredObject::massLoadFwdRef($allInterv, "plageop_id");
  $sejours   = CStoredObject::massLoadFwdRef($allInterv, "sejour_id");
  $patients  = CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadFwdRef($allInterv, 'type_anesth');
  foreach ($allInterv as $_interv) {
    $_interv->loadRefAffectation();
    $_interv->loadRefChir()->loadRefFunction();
    $_interv->loadRefPatient()->loadRefLatestConstantes(null, array("poids", "taille"));
    $_interv->loadRefVisiteAnesth()->loadRefFunction();
    $_interv->loadRefsConsultAnesth()->loadRefConsultation()->loadRefPraticien()->loadRefFunction();
    $_interv->countLinesPostOp();
    $_interv->loadRefTypeAnesth();
  }

  // Création du template
  $smarty = new CSmartyDP();

  $smarty->assign("allIntervByService"   , $allIntervByService);
  $smarty->assign("date"        , $date);
  $smarty->assign("listPrat"    , $listPrat);
  $smarty->assign("services"    , $services);
  $smarty->assign("selPrat"     , $selPrat);
  $smarty->assign("canceled"    , $canceled);
  $smarty->assign("sans_anesth" , $sans_anesth);
  $smarty->assign("sejour_ambu" , $sejour_ambu);
  $smarty->assign("sejour_comp" , $sejour_comp);
  $smarty->assign("hors_plage"  , $hors_plage);

  if ($only_list_anesth) {
    $smarty->display("vw_list_visite_anesth");
  }
  else {
    $smarty->display("vw_idx_visite_anesth");
  }

}

// Non anesthesiste
else {
  // Selection des plages du praticien et de celles de sa spécialité
  $praticien_id = null;
  $function_ids = null;
  $whereChir = "= ''";
  if ($selPraticien->isPraticien()) {
    $whereChir = $selPraticien->getUserSQLClause();

    $praticien_id = $selPraticien->user_id;
    $function_ids = CMbArray::pluck($selPraticien->loadBackRefs("secondary_functions"), "function_id");
    $function_ids[] = $selPraticien->function_id;
  }

  // Planning du mois
  $month_min = CMbDT::format($date, "%Y-%m-01");
  $month_max = CMbDT::format($date, "%Y-%m-31");


  $sql = "SELECT plagesop.*, plagesop.date AS opdate,
        SEC_TO_TIME(SUM(TIME_TO_SEC(operations.temp_operation))) AS duree,
        COUNT(operations.operation_id) AS total,
        SUM(operations.rank_voulu > 0) AS planned_by_chir,
        COUNT(IF(operations.rank > 0, NULLIF(operations.rank, operations.rank_voulu), NULL)) AS order_validated,
        functions_mediboard.text AS nom_function, functions_mediboard.color as color_function
      FROM plagesop
      LEFT JOIN operations
        ON plagesop.plageop_id = operations.plageop_id
          AND operations.annulee = '0'
          AND (
            operations.chir_id $whereChir
            OR operations.chir_2_id $whereChir
            OR operations.chir_3_id $whereChir
            OR operations.chir_4_id $whereChir )
      LEFT JOIN functions_mediboard
        ON functions_mediboard.function_id = plagesop.spec_id
      WHERE (plagesop.chir_id $whereChir OR plagesop.spec_id ".CSQLDataSource::prepareIn($function_ids)."
         OR (plagesop.chir_id IS NULL AND plagesop.spec_id IS NULL AND plagesop.urgence = '1'))
        AND plagesop.date BETWEEN '$month_min' AND '$month_max'
      GROUP BY plagesop.plageop_id
      ORDER BY plagesop.date, plagesop.debut, plagesop.plageop_id";
  $listPlages = array();
  if ($praticien_id) {
    $listPlages = $ds->loadList($sql);
  }

  // Urgences du mois
  $sql = "SELECT operations.*, operations.date AS opdate,
        SEC_TO_TIME(SUM(TIME_TO_SEC(operations.temp_operation))) AS duree,
        COUNT(operations.operation_id) AS total
      FROM operations
      WHERE operations.annulee = '0'
          AND (
            operations.chir_id $whereChir
            OR operations.chir_2_id $whereChir
            OR operations.chir_3_id $whereChir
            OR operations.chir_4_id $whereChir )
        AND operations.plageop_id IS NULL
        AND operations.date BETWEEN '$month_min' AND '$month_max'
      GROUP BY operations.date
      ORDER BY operations.date";
  $listUrgences = array();
  if ($praticien_id) {
    $listUrgences = $ds->loadList($sql);
  }

  $listDays = array();
  foreach ($listPlages as $curr_ops) {
    $listDays[$curr_ops["opdate"]][$curr_ops["plageop_id"]] = $curr_ops;

  }
  foreach ($listUrgences as $curr_ops) {
    $listDays[$curr_ops["opdate"]]["hors_plage"] = $curr_ops;
  }

  ksort($listDays);

  // Création du template
  $smarty = new CSmartyDP();

  $smarty->assign("date"        , $date);
  $smarty->assign("canceled"    , $canceled);
  $smarty->assign("lastmonth"   , $lastmonth);
  $smarty->assign("nextmonth"   , $nextmonth);
  $smarty->assign("listPrat"    , $listPrat);
  $smarty->assign("selPrat"     , $selPrat);
  $smarty->assign("listDays"    , $listDays);

  if (!$refresh) {
    $smarty->display("vw_idx_planning");
  } else {
    $smarty->display('inc_list_plagesop');
  }
}
