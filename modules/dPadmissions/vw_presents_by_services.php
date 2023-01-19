<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$type = CValue::get("type");

// Initialisation de variables
$date = CValue::getOrSession("date", CMbDT::date());

//Mise en slave
CView::enforceSlave();
$month_min = CMbDT::date("first day of +0 month", $date);
$month_max = CMbDT::date("last day of +0 month", $date);
$lastmonth = CMbDT::date("last day of -1 month", $date);
$nextmonth = CMbDT::date("first day of +1 month", $date);

// Initialisation du tableau de jours
$days = [];
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
    $days[$day] = "0";
}

$ds    = CSQLDataSource::get("std");
$group = CGroups::loadCurrent();

$common_query = "SELECT count(*) as total, service.nom as service
  FROM sejour ";

$common_left_join = " LEFT JOIN affectation ON affectation.sejour_id = sejour.sejour_id
  LEFT JOIN service ON service.service_id = affectation.service_id";

$common_where = " WHERE sejour.entree <= '$month_max 00:00:00'
  AND sejour.sortie >= '$month_min'
  AND affectation.affectation_id IS NOT NULL
  AND sejour.annule = '0'
  AND sejour.group_id = '$group->_id' ";

// filtre sur les types d'admission
if ($type == "ambucomp") {
    $common_where .= "  AND (sejour.type = 'ambu' OR sejour.type = 'comp')";
} elseif ($type == "ambucompssr") {
    $common_where .= "  AND (sejour.type = 'ambu' OR sejour.type = 'comp' OR sejour.type = 'ssr')";
} elseif ($type) {
    if ($type !== 'tous') {
        $common_where .= " AND sejour.type = '$type' ";
    }
} else {
    $common_where .= "  AND sejour.type " .
        CSQLDataSource::prepareNotIn(
            CSejour::getTypesSejoursUrgence()
        ) . " AND sejour.type != 'seances'";
}

$common_group_by = "GROUP BY affectation.service_id";

// Liste des présents par jour
$results = $services = [];
foreach ($days as $_date => $num) {
    $query = $common_query . $common_left_join . $common_where;
    $query .= " AND affectation.entree <= '$_date 23:59:00' AND affectation.sortie >= '$_date 00:00:00'";
    //$query .= " AND DATE_FORMAT(sejour.$entree, '%Y-%m-%d') = DATE_FORMAT(sejour.$sortie, '%Y-%m-%d')";
    $query .= $common_group_by;

    $result = $ds->loadList($query);

    foreach ($result as $_result) {
        $results[$_date][$_result["service"]] = $_result["total"];
        if (!isset($services[$_result["service"]])) {
            $services[$_result["service"]] = 0;
        }
        $services[$_result["service"]] += $_result["total"];
    }
    if (isset($results[$_date])) {
        ksort($results[$_date]);
    }
}

ksort($services);
ksort($results);

$smarty = new CSmartyDP();

$smarty->assign("results", $results);
$smarty->assign('lastmonth', $lastmonth);
$smarty->assign('nextmonth', $nextmonth);
$smarty->assign('services', $services);
$smarty->assign('bank_holidays', CMbDT::getHolidays($date));
$smarty->assign('date', $date);

$smarty->display("vw_presents_by_services.tpl");
