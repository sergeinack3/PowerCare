<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

// Initialisation de variables
$date_spec   = array(
  "date",
  "default" => CMbDT::date()
);
$date = CView::get("date", $date_spec, true);


$month_min     = CMbDT::date("first day of +0 month", $date);
$lastmonth     = CMbDT::date("last day of -1 month", $date);
$nextmonth     = CMbDT::date("first day of +1 month", $date);
$bank_holidays = CMbDT::getHolidays($date);
$hier          = CMbDT::date("- 1 day", $date);
$demain        = CMbDT::date("+ 1 day", $date);

$type          = CView::get("type", "str", true);
$type_externe  = CView::get("type_externe", "str default|depart", true);

CView::checkin();

// Initialisation du tableau de jours
$days = array();
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
  $days[$day] = array(
    "num1" => "0",
    "num2" => "0",
    "num3" => "0",
  );
}

// Récupération de la liste des services
$where = array();
$where["externe"]   = "= '1'";
$where["cancelled"] = "= '0'";
$service = new CService();
$services = $service->loadGroupList($where);

// filtre sur les types d'admission
if ($type == "ambucomp") {
  $filterType = "AND (`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp')";
}
elseif ($type == "ambucompssr") {
  $filterType = "AND (`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr')";
}
elseif ($type) {
  $filterType = "AND `sejour`.`type` = '$type'";
}
else {
  $filterType = "AND `sejour`.`type` " . CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence()) ." AND `sejour`.`type` != 'seances'";
}

// filtre sur les services
$filterService = "AND service.service_id ". CSQLDataSource::prepareIn(array_keys($services));

$group = CGroups::loadCurrent();

// Liste des départs par jour
$query = "SELECT DATE_FORMAT(`affectation`.`entree`, '%Y-%m-%d') AS `date`, COUNT(`affectation`.`affectation_id`) AS `num`
  FROM `affectation`
  LEFT JOIN sejour
    ON affectation.sejour_id = sejour.sejour_id
  LEFT JOIN lit
    ON affectation.lit_id = lit.lit_id
  LEFT JOIN chambre
    ON lit.chambre_id = chambre.chambre_id
  LEFT JOIN service
    ON chambre.service_id = service.service_id
  WHERE `affectation`.`entree` BETWEEN '$month_min' AND '$nextmonth'
    AND `sejour`.`group_id` = '$group->_id'
    AND `sejour`.`annule` = '0'
    $filterService
    $filterType
  GROUP BY `date`
  ORDER BY `date`";

foreach ($ds->loadHashList($query) as $day => $num1) {
  $days[$day]["num1"] = $num1;
}

// Liste des retours par jour
$query = "SELECT DATE_FORMAT(`affectation`.`sortie`, '%Y-%m-%d') AS `date`, COUNT(`affectation`.`affectation_id`) AS `num`
  FROM `affectation`
  LEFT JOIN sejour
    ON affectation.sejour_id = sejour.sejour_id
  LEFT JOIN lit
    ON affectation.lit_id = lit.lit_id
  LEFT JOIN chambre
    ON lit.chambre_id = chambre.chambre_id
  LEFT JOIN service
    ON chambre.service_id = service.service_id
  WHERE `affectation`.`sortie` BETWEEN '$month_min' AND '$nextmonth'
    AND `sejour`.`group_id` = '$group->_id'
    AND `sejour`.`annule` = '0'
    $filterService
    $filterType
  GROUP BY `date`
  ORDER BY `date`";
foreach ($ds->loadHashList($query) as $day => $num2) {
  $days[$day]["num2"] = $num2;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("hier"         , $hier);
$smarty->assign("demain"       , $demain);

$smarty->assign("type_externe" , $type_externe);

$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign('date'         , $date);
$smarty->assign('lastmonth'    , $lastmonth);
$smarty->assign('nextmonth'    , $nextmonth);
$smarty->assign('days'         , $days);

$smarty->display('inc_vw_all_permissions.tpl');
