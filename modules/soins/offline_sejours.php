<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Vue offline des séjours
 */
CApp::setMemoryLimit("2200M");
CApp::setTimeLimit(240);

CCanDo::check();

$service_id   = CView::get("service_id", "str");
$date         = CView::get("date", "date default|now");
$embed        = CView::get("embed", "bool");
$day_relative = CView::get('day_relative', 'num');

CView::checkin();
CView::enforceSlave();

$service = new CService();
$service->load($service_id);

if (!is_null($day_relative) && $day_relative >= 0) {
    $date = CMbDT::date("+$day_relative days", $date);
}

$datetime_min = "$date 00:00:00";
$datetime_max = "$date 23:59:59";
$datetime_avg = "$date " . CMbDT::time();

$group = CGroups::loadCurrent();

$sejour = new CSejour();
$where  = [];
$ljoin  = [];

$ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";

$where["sejour.entree"] = "<= '$datetime_max'";
$where["sejour.sortie"] = " >= '$datetime_min'";

if ($service_id == "NP") {
    $where["affectation.affectation_id"] = "IS NULL";
    $where["sejour.group_id"]            = "= '$group->_id'";
} else {
    $where["affectation.entree"]     = "<= '$datetime_max'";
    $where["affectation.sortie"]     = ">= '$datetime_min'";
    $where["affectation.service_id"] = " = '$service_id'";
}

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);

CSejour::massLoadCurrAffectation($sejours, $datetime_avg, $service_id);
CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CMbObject::massLoadRefsNotes($sejours);
CSejour::massLoadNDA($sejours);
/** @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CPatient::massLoadIPP($patients);
foreach ($sejours as $sejour) {
    $patient = $sejour->loadRefPatient();
    $sejour->loadRefPraticien();
    $sejour->checkDaysRelative($date);
    $sejour->loadRefsNotes();
}

if ($service_id == "NP") {
    $sorter_sejour = CMbArray::pluck($sejours, "entree_prevue");
    array_multisort(
        $sorter_sejour,
        SORT_ASC,
        $sejours
    );
} else {
    $sorter_patient     = CMbArray::pluck($sejours, "_ref_patient", "nom");
    $sorter_affectation = CMbArray::pluck($sejours, "_ref_curr_affectation", "_ref_lit", "_view");
    array_multisort(
        $sorter_affectation,
        SORT_ASC,
        $sorter_patient,
        SORT_ASC,
        $sejours
    );
}
$period            = CAppUI::conf("soins offline_sejour period", $group);
$dossiers_complets = [];

$sejours_date_reelle_ids = [];
foreach ($sejours as $sejour) {
    $params = [
        "sejour_id"  => $sejour->_id,
        "dialog"     => 1,
        "offline"    => 1,
        "in_modal"   => 0,
        "show_forms" => 1,
        "embed"      => $embed,
        "period"     => $period,
    ];

    $dossiers_complets[$sejour->_id] = CApp::fetch("soins", "print_dossier_soins", $params);
    if ($sejour->sortie_reelle !== null) {
        $sejours_date_reelle_ids[$sejour->_id] = $sejour->_id;
    }
}

$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("hour", CMbDT::time());
$smarty->assign("service", $service);
$smarty->assign("sejours", $sejours);
$smarty->assign("sejours_date_reelle_ids", $sejours_date_reelle_ids);
$smarty->assign("dossiers_complets", $dossiers_complets);

$smarty->display("offline_sejours");
