<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$ds = CSQLDataSource::get("std");

// Initialisation de variables
$date_spec   = array(
  "date",
  "default" => CMbDT::date()
);
$date = CView::get("date", $date_spec, true);

CView::checkin();

$month_min     = CMbDT::date("first day of +0 month", $date);
$lastmonth     = CMbDT::date("last day of -1 month", $date);
$nextmonth     = CMbDT::date("first day of +1 month", $date);
$bank_holidays = CMbDT::getHolidays($date);
$hier          = CMbDT::date("- 1 day", $date);
$demain        = CMbDT::date("+ 1 day", $date);

// Initialisation du tableau de jours
$days = array();
for ($day = $month_min; $day < $nextmonth; $day = CMbDT::date("+1 DAY", $day)) {
  $days[$day] = array(
    "total" => "0",
  );
}

// Récupération de la liste des anesthésistes
$mediuser = new CMediusers;
$anesthesistes = $mediuser->loadAnesthesistes(PERM_READ);

$consult = new CConsultation();

// Liste des admissions par jour
//foreach ($ds->loadHashList($sql) as $day => $num1) {
//  $days[$day]["num1"] = $num1;
//}

// Récupération du nombre de consultations par jour
$ljoin = array();
$ljoin["plageconsult"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";
$where = array();
$where["consultation.patient_id"] = "IS NOT NULL";
$where["consultation.annule"] = "= '0'";
$where["plageconsult.chir_id"] = CSQLDataSource::prepareIn(array_keys($anesthesistes));
$where["plageconsult.date"] = "BETWEEN '$month_min' AND '$nextmonth'";
$order = "plageconsult.date";
$groupby = "plageconsult.date";

$fields = array("plageconsult.date");

$listMonth = $consult->countMultipleList($where, $order, $groupby, $ljoin, $fields);
foreach ($listMonth as $_day) {
  $days[$_day["date"]]["total"] = $_day["total"];
}

$total = array_sum(CMbArray::pluck($days, "total"));

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("hier"         , $hier);
$smarty->assign("demain"       , $demain);
$smarty->assign("date"         , $date);
$smarty->assign("lastmonth"    , $lastmonth);
$smarty->assign("nextmonth"    , $nextmonth);
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign("days"         , $days);
$smarty->assign("total"        , $total);

$smarty->display('inc_vw_all_preadmissions.tpl');
