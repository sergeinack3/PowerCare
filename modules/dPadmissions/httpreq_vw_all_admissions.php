<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$ds = CSQLDataSource::get("std");

$sejour = new CSejour();
// Initialisation de variables
$date_spec = array(
  "date",
  "default" => CMbDT::date()
);
$date      = CView::get("date", $date_spec, true);

$month_min     = CMbDT::date("first day of +0 month", $date);
$lastmonth     = CMbDT::date("last day of -1 month", $date);
$nextmonth     = CMbDT::date("first day of +1 month", $date);
$bank_holidays = CMbDT::getHolidays($date);

$selAdmis              = CView::get("selAdmis", "str default|0", true);
$selSaisis             = CView::get("selSaisis", "str default|0", true);
$services_ids          = CView::get("services_ids", "str", true);
$sejours_ids           = CView::get("sejours_ids", "str", true);
$enabled_service       = CView::get("active_filter_services", "bool default|0", true);
$prat_id               = CView::get("prat_id", "num", true);
$type_pec_spec         = array(
  "str",
  "default" => $sejour->_specs["type_pec"]->_list
);
$type_pec              = CView::get("type_pec", $type_pec_spec, true);
$circuits_ambu         = CView::get("circuit_ambu", array('str', 'default' => $sejour->_specs["circuit_ambu"]->_list), true);
$date_interv_eg_entree = CView::get("date_interv_eg_entree", "bool default|0", true);
$reglement_dh          = CView::get('reglement_dh', 'enum list|all|payed|not_payed');
CView::checkin();
CView::enforceSlave();

$type_pref = array();

// Liste des types d'admission possibles
$list_type_admission = $sejour->_specs["_type_admission"]->_list;

if (is_array($circuits_ambu)) {
  CMbArray::removeValue("", $circuits_ambu);
}

if (is_array($services_ids)) {
  CMbArray::removeValue("", $services_ids);
}

if (is_array($sejours_ids)) {
  CMbArray::removeValue("", $sejours_ids);

  // recupere les préférences des differents types de séjours selectionnés par l'utilisateur
  foreach ($sejours_ids as $key) {
    if ($key != 0) {
      $type_pref[] = $list_type_admission[$key];
    }
  }
}

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);

// Initialisation du tableau de jours
$days = array();
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
  $days[$day] = array(
    "admissions"               => "0",
    "admissions_non_effectuee" => "0",
    "admissions_non_preparee"  => "0",
  );
}

$group    = CGroups::loadCurrent();
$where    = array();
$leftjoin = array();

// filtre sur les services
if (count($services_ids) && $enabled_service) {
  $leftjoin["affectation"]         = " affectation.sejour_id = sejour.sejour_id AND affectation.entree = sejour.entree";
  $where[] = "affectation.service_id " . CSQLDataSource::prepareIn($services_ids) .
             " OR (sejour.service_id " . CSQLDataSource::prepareIn($services_ids) . " AND affectation.affectation_id IS NULL)";
}

if (!(in_array('', $type_pec) && count($type_pec) == 1)) {
  // filtre sur les types pec des sejours
  $where[] = "sejour.type_pec " . CSQLDataSource::prepareIn($type_pec) . 'OR sejour.type_pec IS NULL';
}

// Filtre sur les types d'admission
if (count($type_pref)) {
  $where["sejour.type"] = CSQLDataSource::prepareIn($type_pref);
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ["seances"]));
}

// filtre sur le type de circuit en ambulatoire
if (CAppUI::gconf("dPplanningOp CSejour show_circuit_ambu") && $circuits_ambu && count($circuits_ambu)) {
  $where["sejour.circuit_ambu"] = CSQLDataSource::prepareIn($circuits_ambu) . ' OR sejour.circuit_ambu IS NULL';
}

// filtre sur le praticien
if ($prat_id) {
  $user = CMediusers::get($prat_id);

  if ($user->isAnesth()) {
    $leftjoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
    $leftjoin['plagesop'] = 'plagesop.plageop_id = operations.plageop_id';
    $where[] = " operations.anesth_id = '$prat_id' OR plagesop.anesth_id = '$prat_id' OR sejour.praticien_id = '$prat_id'";
  }
  else {
    $where['sejour.praticien_id'] = " = '$prat_id'";
  }
}

$where["sejour.entree"]   = " BETWEEN '$month_min' AND '$nextmonth'";
$where["sejour.group_id"] = " = '$group->_id'";
$where["sejour.annule"]   = " = '0'";

if ($date_interv_eg_entree) {
  $leftjoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
  $where[] = "operations.date = DATE(sejour.entree_prevue)";
}

if ($reglement_dh && $reglement_dh != 'all') {
  if (!isset($ljoin["operations"])) {
    $leftjoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
  }
  if ($reglement_dh == 'payed') {
    $where[] = "((operations.depassement > 0 AND operations.reglement_dh_chir != 'non_regle') 
        OR operations.depassement = 0 OR operations.depassement IS NULL)
      AND ((operations.depassement_anesth > 0 AND operations.reglement_dh_anesth != 'non_regle') 
        OR operations.depassement_anesth = 0 OR operations.depassement_anesth IS NULL)
      AND (operations.depassement > 0 OR operations.depassement_anesth > 0)";
  }
  else {
    $where[] = "(operations.depassement > 0 AND operations.reglement_dh_chir = 'non_regle')
      OR (operations.depassement_anesth > 0 AND operations.reglement_dh_anesth = 'non_regle')";
  }
}

// Liste des admissions par jour
$request = new CRequest();
$request->addSelect(array("DATE_FORMAT(sejour.entree, '%Y-%m-%d') AS 'date'", "COUNT(DISTINCT sejour.sejour_id) AS 'num'"));
$request->addTable("sejour");
$request->addForceIndex('entree');
$request->addWhere($where);
$request->addLJoin($leftjoin);
$request->addGroup("date");
$request->addOrder("date");
foreach ($ds->loadHashList($request->makeSelect()) as $day => $num1) {
  $days[$day]["admissions"] = $num1;
}

// Liste des admissions non préparées
$request->where                = [];
$where["sejour.entree_preparee"] = " = '0'";
$request->addWhere($where);
foreach ($ds->loadHashList($request->makeSelect()) as $day => $num3) {
  $days[$day]["admissions_non_preparee"] = $num3;
}

// Liste des admissions non effectuées par jour
unset($where['sejour.entree']);
unset($where['sejour.entree_preparee']);
$request->where                = [];
$request->forceindex           = [];
$request->addForceIndex('entree_prevue');
$where["sejour.entree_prevue"] = " BETWEEN '$month_min' AND '$nextmonth'";
$where["sejour.entree_reelle"] = " IS NULL";
$request->addWhere($where);
foreach ($ds->loadHashList($request->makeSelect()) as $day => $num2) {
  $days[$day]["admissions_non_effectuee"] = $num2;
}

$totaux = array(
  "admissions"               => "0",
  "admissions_non_effectuee" => "0",
  "admissions_non_preparee"  => "0",
);

foreach ($days as $day) {
  $totaux["admissions"] += $day["admissions"];
  $totaux["admissions_non_effectuee"] += $day["admissions_non_effectuee"];
  $totaux["admissions_non_preparee"] += $day["admissions_non_preparee"];
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("hier"            , $hier);
$smarty->assign("demain"          , $demain);
$smarty->assign("selAdmis"        , $selAdmis);
$smarty->assign("selSaisis"       , $selSaisis);
$smarty->assign("bank_holidays"   , $bank_holidays);
$smarty->assign('date'            , $date);
$smarty->assign('lastmonth'       , $lastmonth);
$smarty->assign('nextmonth'       , $nextmonth);
$smarty->assign('days'            , $days);
$smarty->assign('totaux'          , $totaux);
$smarty->assign('enabled_service' , $enabled_service);
$smarty->display('inc_vw_all_admissions');
