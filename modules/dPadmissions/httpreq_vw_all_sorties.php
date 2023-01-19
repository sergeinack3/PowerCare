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
$date_spec = [
    "date",
    "default" => CMbDT::date(),
];
$date      = CView::get("date", $date_spec, true);

$month_min     = CMbDT::date("first day of +0 month", $date);
$lastmonth     = CMbDT::date("last day of -1 month", $date);
$nextmonth     = CMbDT::date("first day of +1 month", $date);
$bank_holidays = CMbDT::getHolidays($date);

$current_m       = CView::get("current_m", "str");
$selSortis       = CView::get("selSortis", "str default|0", true);
$services_ids    = CView::get("services_ids", "str", true);
$sejours_ids     = CView::get("sejours_ids", "str", true);
$enabled_service = CView::get("active_filter_services", "bool default|0", true);
$prat_id         = CView::get("prat_id", "num", true);
$type_pec_spec   = [
    "str",
    "default" => $sejour->_specs["type_pec"]->_list,
];
$type_pec        = CView::get("type_pec", $type_pec_spec);
$only_confirmed  = CView::get("only_confirmed", "str", true);
$reglement_dh    = CView::get('reglement_dh', 'enum list|all|payed|not_payed');
$circuits_ambu   = CView::get("circuit_ambu", ['str', 'default' => $sejour->_specs["circuit_ambu"]->_list], true);

CView::checkin();
CView::enforceSlave();

$type_pref = [];

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

// Initialisation des totaux
$days = [];
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
    $days[$day]["sorties"]                = 0;
    $days[$day]["sorties_non_effectuees"] = 0;
    $days[$day]["sorties_non_preparees"]  = 0;
    $days[$day]["sorties_non_facturees"]  = 0;
}

// Filtre sur les types d'admission
if (count($type_pref)) {
    $filterType = "AND sejour.type " . CSQLDataSource::prepareIn($type_pref);
} else {
    $filterType = "AND sejour.type " . CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ["seances"]));
}

// filtre sur les services
$leftjoinService = $filterService = "";
if ($enabled_service && count($services_ids)) {
    $leftjoinService = "LEFT JOIN affectation
                        ON affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie";
    $filterService   = "AND affectation.service_id " . CSQLDataSource::prepareIn($services_ids);
}

// filtre sur le praticiens
$filterPrat   = "";
$leftJoinPrat = "";
if ($prat_id) {
    $user = CMediusers::get($prat_id);

    if ($user->isAnesth()) {
        $leftJoinPrat = "LEFT JOIN operations ON sejour.sejour_id = operations.sejour_id
                     LEFT JOIN plagesop ON plagesop.plageop_id = operations.plageop_id";
        $filterPrat   = "AND (operations.anesth_id = '$prat_id' OR plagesop.anesth_id = '$prat_id' OR sejour.praticien_id = '$prat_id')";
    } else {
        $filterPrat = "AND sejour.praticien_id = '$prat_id'";
    }
}

$filterConfirmed = "";
if ($only_confirmed != "") {
    $filterConfirmed = "AND sejour.confirme " . ($only_confirmed ? "IS NOT NULL" : "IS NULL");
}

$filterReglement   = "";
$leftJoinReglement = "";
if ($reglement_dh && $reglement_dh != 'all') {
    if (!$leftJoinPrat) {
        $leftJoinReglement = 'LEFT JOIN operations ON sejour.sejour_id = operations.sejour_id';
    }
    if ($reglement_dh == 'payed') {
        $filterReglement = "AND (((operations.depassement > 0 AND operations.reglement_dh_chir != 'non_regle') 
        OR operations.depassement = 0 OR operations.depassement IS NULL)
      AND ((operations.depassement_anesth > 0 AND operations.reglement_dh_anesth != 'non_regle') 
        OR operations.depassement_anesth = 0 OR operations.depassement_anesth IS NULL)
      AND (operations.depassement > 0 OR operations.depassement_anesth > 0))";
    } else {
        $filterReglement = "AND ((operations.depassement > 0 AND operations.reglement_dh_chir = 'non_regle')
      OR (operations.depassement_anesth > 0 AND operations.reglement_dh_anesth = 'non_regle'))";
    }
}

$filterPeC = '';
if (!(in_array('', $type_pec) && count($type_pec) == 1)) {
    $filterPeC = "AND (sejour.type_pec " . CSQLDataSource::prepareIn($type_pec) . 'OR sejour.type_pec IS NULL)';
}

// filtre sur le type de circuit en ambulatoire
$filterCircuit = '';
if (CAppUI::gconf("dPplanningOp CSejour show_circuit_ambu") && $circuits_ambu && count($circuits_ambu)) {
    $filterCircuit = "AND (sejour.circuit_ambu " . CSQLDataSource::prepareIn(
            $circuits_ambu
        ) . " OR sejour.circuit_ambu IS NULL)";
}

$group = CGroups::loadCurrent();

// Listes des sorties par jour
$query = "SELECT DATE_FORMAT(`sejour`.`sortie`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
          FROM `sejour`
          $leftjoinService
          $leftJoinPrat
          $leftJoinReglement
          WHERE `sejour`.`sortie` BETWEEN '$month_min' AND '$nextmonth'
            AND `sejour`.`group_id` = '$group->_id'
            AND `sejour`.`annule` = '0'
            $filterType
            $filterService
            $filterPrat
            $filterConfirmed
            $filterReglement
            $filterPeC
            $filterCircuit
          GROUP BY `date`
          ORDER BY `date`";

foreach ($ds->loadHashList($query) as $day => $num1) {
    $days[$day]["sorties"] = $num1;
}

// Liste des sorties non effectuées par jour
$query = "SELECT DATE_FORMAT(`sejour`.`sortie`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
          FROM `sejour`
          $leftjoinService
          $leftJoinPrat
          $leftJoinReglement
          WHERE `sejour`.`sortie` BETWEEN '$month_min' AND '$nextmonth'
            AND `sejour`.`group_id` = '$group->_id'
            AND `sejour`.`sortie_reelle` IS NULL
            AND `sejour`.`annule` = '0'
            $filterType
            $filterService
            $filterPrat
            $filterConfirmed
            $filterReglement
            $filterPeC
            $filterCircuit
          GROUP BY `date`
          ORDER BY `date`";

foreach ($ds->loadHashList($query) as $day => $num2) {
    $days[$day]["sorties_non_effectuees"] = $num2;
}

// Unprepared discharges
$query = "SELECT DATE_FORMAT(`sejour`.`sortie`, '%Y-%m-%d') AS `date`, COUNT(`sejour`.`sejour_id`) AS `num`
          FROM `sejour`
          $leftjoinService
          $leftJoinPrat
          $leftJoinReglement
          WHERE `sejour`.`sortie` BETWEEN '$month_min' AND '$nextmonth'
            AND `sejour`.`group_id` = '$group->_id'
            AND `sejour`.`sortie_preparee` = '0'
            AND `sejour`.`annule` = '0'
            $filterType
            $filterService
            $filterPrat
            $filterConfirmed
            $filterReglement
            $filterPeC
            $filterCircuit
          GROUP BY `date`
          ORDER BY `date`";
foreach ($ds->loadHashList($query) as $day => $_nb_non_preparees) {
    $days[$day]["sorties_non_preparees"] = $_nb_non_preparees;
}

$totaux = [
    "sorties"                => "0",
    "sorties_non_preparees"  => "0",
    "sorties_non_effectuees" => "0",
    "sorties_non_facturees"  => "0",

];

foreach ($days as $day) {
    $totaux["sorties"]                += $day["sorties"];
    $totaux["sorties_non_preparees"]  += $day["sorties_non_preparees"];
    $totaux["sorties_non_facturees"]  += $day["sorties_non_facturees"];
    $totaux["sorties_non_effectuees"] += $day["sorties_non_effectuees"];
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("current_m", $current_m);

$smarty->assign("hier", $hier);
$smarty->assign("demain", $demain);
$smarty->assign("selSortis", $selSortis);
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign('date', $date);
$smarty->assign('lastmonth', $lastmonth);
$smarty->assign('nextmonth', $nextmonth);
$smarty->assign('days', $days);
$smarty->assign('enabled_service', $enabled_service);
$smarty->assign("totaux", $totaux);
$smarty->assign('circuits_ambu', $circuits_ambu);

$smarty->display('inc_vw_all_sorties.tpl');
