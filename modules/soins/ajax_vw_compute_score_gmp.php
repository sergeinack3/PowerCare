<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\CApp;
use Ox\Mediboard\Cabinet\CExamGir;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();
$_date_min  = CView::get("_date_min", ["date", "default" => CMbDT::date('-7 day')], true);
$_date_max  = CView::get("_date_max", ["date", "default" => CMbDT::date('+1 day')], true);
$service_id = CView::get("_service_id", 'str');
CView::checkin();

if (!$_date_min) {
    $_date_min = CMbDT::date("first day of January", CMbDT::date("-1 YEAR"));
}

if (!$_date_max) {
    $_date_max = CMbDT::date("last day of December", $_date_min);
}

if ($service_id == 'NP') {
    $service = null;
} else {
    $service = CService::find($service_id);
}

$group = CGroups::loadCurrent();

$ljoin = [
    "sejour"      => "sejour.sejour_id = examgir.sejour_id",
    "affectation" => "affectation.sejour_id = sejour.sejour_id",
];

$exam_gir = new CExamGir();
$ds       = $exam_gir->getDS();

$where = [
    "sejour.group_id" => $ds->prepare('= ?', $group->_id),
    "examgir.date"    => $ds->prepare('BETWEEN ?1 AND ?2', "$_date_min 00:00:00", "$_date_max 23:59:59"),
];

if ($service_id == 'NP') {
    $where["affectation.affectation_id"] = " IS NULL";
} elseif ($service_id) {
    $where["affectation.service_id"] = $ds->prepare('= ?', $service_id);
}

$exam_girs = $exam_gir->loadList($where, null, null, "sejour.sejour_id", $ljoin);

$points = [
    "gir_1" => 1000,
    "gir_2" => 840,
    "gir_3" => 660,
    "gir_4" => 420,
    "gir_5" => 250,
    "gir_6" => 70,
];

$gir_points = [
    "gir_1" => [
        "points"   => 0,
        "patients" => 0,
    ],
    "gir_2" => [
        "points"   => 0,
        "patients" => 0,
    ],
    "gir_3" => [
        "points"   => 0,
        "patients" => 0,
    ],
    "gir_4" => [
        "points"   => 0,
        "patients" => 0,
    ],
    "gir_5" => [
        "points"   => 0,
        "patients" => 0,
    ],
    "gir_6" => [
        "points"   => 0,
        "patients" => 0,
    ],
];

$totaux = [
    "gir_points" => 0,
    "patients"   => 0,
    "GMP"        => 0,
];

// Calculation for patients aged 60 and over
foreach ($exam_girs as $_exam_gir) {
    $sejour  = $_exam_gir->loadRefSejour();
    $patient = $sejour->loadRefPatient();

    $gir_points["gir_$_exam_gir->score_gir"]["points"] += $points["gir_$_exam_gir->score_gir"];
    $gir_points["gir_$_exam_gir->score_gir"]["patients"]++;
}

foreach ($gir_points as $_gir) {
    $totaux["gir_points"] += $_gir["points"];
    $totaux["patients"]   += $_gir["patients"];
}

$totaux["GMP"] = ($totaux["patients"] == 0) ? 0 : round($totaux["gir_points"] / $totaux["patients"]);

$smarty = new CSmartyDP();
$smarty->assign("gir_points", $gir_points);
$smarty->assign("totaux", $totaux);
$smarty->assign("_date_min", $_date_min);
$smarty->assign("_date_max", $_date_max);
$smarty->assign("service", $service);
$smarty->assign("service_id", $service_id);
$smarty->display("inc_vw_compute_score_gmp");
