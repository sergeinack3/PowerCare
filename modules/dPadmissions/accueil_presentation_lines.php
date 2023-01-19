<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$filter = new CSejour();

$filter->_statut_pec  = CView::get("_statut_pec", "str", true);
$filter->praticien_id = CView::get("praticien_id", "num", true);
$type_pec             = CView::get("type_pec", "str default|".$filter->_specs["type_pec"]->list);
$mode_bandeau         = CView::get("mode_bandeau", "bool default|0");
$enabled_services     = CView::get("enabled_services", "bool default|0");
$period               = CView::get("period", "str", true);
$types_admission      = CSejour::getTypeSejourIdsPref();

$now      = CMbDT::date();
$date_min = $now." 00:00:00";
$date_max = $now." 23:59:59";

$ljoin = array();
$where = array(
  "entree_reelle" => " BETWEEN '$date_min' AND '$date_max'",
  "pec_service"   => " IS NULL"
);
if ($enabled_services) {
  $services_filter = CService::getServicesIdsPref();
  if (count($services_filter) > 0) {
    $ljoin["affectation"] = " affectation.sejour_id = sejour.sejour_id AND affectation.entree = sejour.entree";
    $where[] = "affectation.service_id ". CSQLDataSource::prepareIn($services_filter)
      .((in_array("NP", $services_filter)) ? " OR affectation.affectation_id IS NULL" : "");
  }
}
CView::checkin();

if (is_array($types_admission) && count($types_admission) > 0) {
  $list_type_admission = $filter->_specs["_type_admission"]->_list;
  $filter_admission = array();
  foreach ($types_admission as $_key_admission) {
    $filter_admission[] = $list_type_admission[$_key_admission];
  }
  $where["sejour.type"] = CSQLDataSource::prepareIn($filter_admission);
}
if ($type_pec) {
  $where["sejour.type_pec"] = CSQLDataSource::prepareIn($type_pec);
}
if ($filter->_statut_pec) {
  if ($filter->_statut_pec == "attente") {
    $where["pec_accueil"] = " IS NULL";
  }
  elseif ($filter->_statut_pec == "en_cours") {
    $where["pec_accueil"] = " IS NOT NULL";
  }
}
if ($filter->praticien_id) {
  $user = CMediusers::get($filter->praticien_id);

  if ($user->isAnesth()) {
    $ljoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
    $ljoin['plagesop'] = 'plagesop.plageop_id = operations.plageop_id';
    $where[] = " operations.anesth_id = '$filter->praticien_id'
                   OR plagesop.anesth_id = '$filter->praticien_id'
                   OR sejour.praticien_id = '$filter->praticien_id'";
  }
  else {
    $where['sejour.praticien_id'] = " = '$filter->praticien_id'";
  }
}

if ($period) {
  $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");

  $where["sejour.entree_prevue"] = (($period === "matin") ? "<=" : ">" )." '$now $hour'";
}

$sejours = $filter->loadList($where, "entree_reelle ASC", null, null, $ljoin);
$lits_list = array();
$sejours_pages = array(0=>array());
$sejours_par_page = CAppUI::gconf("dPadmissions presentation nb_elements_affiche");
$data_bandeau = array();
$now = CMbDT::date();
$now_time = CMbDT::dateTime();

CSejour::massLoadCurrAffectation($sejours);

foreach ($sejours as $_sejour) {
  if (isset($sejours_pages[count($sejours_pages)-1])
      && (count($sejours_pages[count($sejours_pages)-1]) == $sejours_par_page)
  ) {
    $sejours_pages[count($sejours_pages)] = array();
  }

  $patient = $_sejour->loadRefPatient();
  $patient->nom = substr($_sejour->_ref_patient->nom, 0, 3);
  $patient->prenom = substr($_sejour->_ref_patient->prenom, 0, 3);
  $patient->naissance = CMbDT::format($patient->naissance, "%d/%m");

  $curr_affectation = $_sejour->_ref_curr_affectation;
  $lit = $curr_affectation->loadRefLit();
  $chambre = $lit->loadRefChambre();
  $chambre->loadRefService();

  if (CModule::getActive("hotellerie")) {
    $cleanup = $lit->loadLastCleanup($now);
    $cleanup->_waiting_time = false;
    if ($cleanup->_id) {
      if ($cleanup->datetime_end && CMbDT::minutesRelative($cleanup->datetime_end, $now_time) >= 0) {
        $time_diff_cleanup = CMbDT::minutesRelative($cleanup->datetime_end, $now_time);
        $time_diff_sejour = CMbDT::minutesRelative($_sejour->entree_reelle, $now_time);
        $time_diff = min($time_diff_cleanup, $time_diff_sejour);
        $cleanup->_status = "ready";
        $cleanup->_waiting_time = (($time_diff > 60) ? (floor($time_diff/60)) . "h " : "" ).($time_diff%60)."m";
        if ($mode_bandeau) {
          $data_bandeau[] = CAppUI::tr(
            "admissions-presentation waited-to-the-service",
            array(
              $patient->prenom,
              $patient->nom,
              (($patient->sexe === "f") ? "e" : ""),
              $chambre->_ref_service->_view,
              $chambre->_view
            )
          );
        }
      }
      else {
        $cleanup->_status = "cleaning";
      }
    }
    else {
      $cleanup->_status = "assigning";
    }
  }

  $sejours_pages[count($sejours_pages)-1][] = $_sejour;
}

if ($mode_bandeau) {
  CApp::json($data_bandeau);
}
else {
  $smarty = new CSmartyDP();

  $smarty->assign("sejours_pages", $sejours_pages);

  $smarty->display("accueil_presentation_lines");
}

