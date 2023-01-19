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
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;

CCanDo::checkRead();

$service_id   = CView::get("service_id", "str");
$date         = CView::get("date", "date default|" . CMbDT::date());
$day_relative = CView::get('day_relative', 'num');

CView::checkin();
CView::enforceSlave();

// Le service_id en get lors du fetch pour le plan de soins pose problème
unset($_GET["service_id"]);

$service = new CService();
$service->load($service_id);

if (!is_null($day_relative) && $day_relative >= 0) {
  $date = CMbDT::date("+$day_relative days", $date);
}

$datetime_min = "$date 00:00:00";
$datetime_max = "$date 23:59:59";
$datetime_avg = "$date " . CMbDT::time();

$date_tolerance = CAppUI::conf("dPurgences date_tolerance");
$date_before    = CMbDT::date("-$date_tolerance DAY", $date);
$date_after     = CMbDT::date("+1 DAY", $date);

$group = CGroups::loadCurrent();

$sejour = new CSejour();
$where  = array();
$ljoin  = array();

$ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";

$where["sejour.entree"]   = "<= '$datetime_max'";
$where["sejour.sortie"]   = " >= '$datetime_min'";
$where["sejour.group_id"] = "= '$group->_id'";

switch ($service_id) {
  case "NP":
    $where["affectation.affectation_id"] = "IS NULL";
    $where["sejour.group_id"]            = "= '$group->_id'";
    break;
  case "urgence":
    $ljoin["rpu"]                  = "sejour.sejour_id = rpu.sejour_id";
    $ljoin["patients"]             = "sejour.patient_id = patients.patient_id";
    $where[]                       = "sejour.entree BETWEEN '$date' AND '$date_after'
                OR (sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$date_before' AND '$date_after' AND sejour.annule = '0')";
    $where[]                       = CAppUI::pref("showMissingRPU") ?
      "sejour.type " . CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence()) . " OR rpu.rpu_id IS NOT NULL" :
      "rpu.rpu_id IS NOT NULL";
    $where["sejour.sortie_reelle"] = "IS NULL";
    $where["sejour.annule"]        = " = '0'";
    if (CAppUI::conf("dPurgences create_sejour_hospit")) {
      $where["rpu.mutation_sejour_id"] = "IS NULL";
    }
    break;
  default:
    $where["affectation.entree"]     = "<= '$datetime_max'";
    $where["affectation.sortie"]     = ">= '$datetime_min'";
    $where["affectation.service_id"] = " = '$service_id'";
}

$patients_offline = array();

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);

CSejour::massLoadNDA($sejours);
/** @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CPatient::massLoadIPP($patients);
CStoredObject::massCountBackRefs($sejours, "operations");

// Recherche de transmissions // observations // consultations
$datetime_delta = CMbDT::date("-3 days", $datetime_avg);
$sejours_ids    = CMbArray::pluck($sejours, "_id");

$where = array("sejour_id" => CSQLDataSource::prepareIn($sejours_ids));

// Transmissions
$whereTrans                      = $where;
$whereTrans["libelle_atc"]       = "IS NOT NULL";
$whereTrans["date"]              = "BETWEEN '$datetime_delta' AND '$datetime_avg'";
$whereTrans["cancellation_date"] = " IS NULL";
$transmission                    = new CTransmissionMedicale();
$transmissions                   = $transmission->loadList($whereTrans, "date");

$whereTrans                      = $where;
$whereTrans["date"]              = "BETWEEN '$datetime_delta' AND '$datetime_avg'";
$whereTrans["object_id"]         = "IS NOT NULL";
$whereTrans["cancellation_date"] = " IS NULL";
$transmission                    = new CTransmissionMedicale();
$transmissions                   = array_merge($transmissions, $transmission->loadList($whereTrans, "date"));

$whereTrans                      = $where;
$whereTrans["date"]              = "BETWEEN '$datetime_delta' AND '$datetime_avg'";
$whereTrans[]                    = "object_id IS NULL and libelle_atc IS NULL";
$whereTrans["cancellation_date"] = " IS NULL";
$transmission                    = new CTransmissionMedicale();
$transmissions                   = array_merge($transmissions, $transmission->loadList($whereTrans, "date"));
CStoredObject::massLoadFwdRef($transmissions, "user_id");
CMbArray::pluckSort($transmissions, SORT_ASC, "date");

// Observations
$observation                   = new CObservationMedicale();
$whereObs                      = $where;
$whereObs["date"]              = "BETWEEN '$datetime_delta' AND '$datetime_avg'";
$whereObs["cancellation_date"] = " IS NULL";
$observations                  = $observation->loadList($whereObs, "date");
CStoredObject::massLoadFwdRef($observations, "user_id");

// Consultations
$consultation                      = new CConsultation();
$whereConsult                      = $where;
$whereConsult["plageconsult.date"] = "BETWEEN '$datetime_delta' AND '$datetime_avg'";
$ljoin                             = array(
  "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"
);
$consultations                     = $consultation->loadList($whereConsult, "plageconsult.date", null, null, $ljoin);
CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");


$smarty_cstes = new CSmartyDP("modules/dPpatients");
$smarty_cstes->assign("empty_lines", 2);
$smarty_cstes->assign("offline", 1);

// Constantes des 12 dernières heures
$where_cste = array("datetime" => "BETWEEN '" . CMbDT::subDateTime("12:00:00", $datetime_avg) . "' AND '$datetime_avg'");

$plan_soins_active = CPrescription::isPlanSoinsActive();

CPrescriptionLine::$_offline_lite    = true;
CPrescriptionLineMix::$_offline_lite = true;
CPrescription::$_offline_lite        = true;

foreach ($sejours as $_sejour) {
  CView::enforceSlave();

  $patient = $_sejour->loadRefPatient();

  $_sejour->loadRefPraticien();
  $_sejour->loadJourOp($date);

  if ($service_id == "urgence") {
    $_sejour->_veille = CMbDT::date($_sejour->entree) != $date;
    $rpu = $_sejour->loadRefRPU();
    $rpu->loadRefIDEResponsable();
    $rpu->loadRefConsult();
  }
  $patients_offline[$patient->_guid]["sejour"] = $_sejour;

  // Transmissions
  $patients_offline[$patient->_guid]["transmissions"] = array();

  // Regroupement par cible
  $trans_sejour = array();
  foreach ($transmissions as $_trans) {
    if ($_trans->sejour_id != $_sejour->_id) {
      continue;
    }
    $_trans->loadTargetObject();
    $_trans->calculCibles();
    $_trans->loadRefUser();

    $sort_key        = $_trans->date;
    $sort_key_before = CMbDT::dateTime("-1 SECOND", $_trans->date);
    $sort_key_after  = CMbDT::dateTime("+1 SECOND", $_trans->date);

    if (!isset($trans_sejour[$_trans->_cible][$sort_key])
      && !isset($trans_sejour[$_trans->_cible][$sort_key_before])
      && !isset($trans_sejour[$_trans->_cible][$sort_key_after])
    ) {
      $trans_sejour[$_trans->_cible][$sort_key]    = array("data" => array(), "action" => array(), "result" => array());
      $trans_sejour[$_trans->_cible][$sort_key][0] = $_trans;
    }
    if (isset($trans_sejour[$_trans->_cible][$sort_key_before])) {
      $trans_sejour[$_trans->_cible][$sort_key_before][$_trans->type][] = $_trans;
    }
    elseif (isset($trans_sejour[$_trans->_cible][$sort_key_after])) {
      $trans_sejour[$_trans->_cible][$sort_key_after][$_trans->type][] = $_trans;
    }
    else {
      $trans_sejour[$_trans->_cible][$sort_key][$_trans->type][] = $_trans;
    }
  }

  // On garde la dernière transmission par cible
  // et suppression des transmissions verrouillées
  foreach ($trans_sejour as $cible => $_trans_by_date) {
    $trans  = end($_trans_by_date);
    $locked = false;
    foreach ($trans as $key => $_trans) {
      if ($key == "0") {
        continue;
      }
      foreach ($_trans as $key_ => $__trans) {
        if ($__trans->locked) {
          $locked = true;
        }
      }
    }
    if ($locked) {
      continue;
    }
    $date_trans                                                           = array_keys($_trans_by_date);
    $patients_offline[$patient->_guid]["transmissions"][end($date_trans)] = $trans;
  }

  // Tri par date décroissante
  krsort($patients_offline[$patient->_guid]["transmissions"]);

  // Observations
  $patients_offline[$patient->_guid]["observations"] = array();

  foreach ($observations as $_observation) {
    if ($_observation->sejour_id != $_sejour->_id) {
      continue;
    }
    $_observation->loadRefUser()->loadRefFunction();
    $_observation->loadTargetObject();
    $patients_offline[$patient->_guid]["observations"][$_observation->_ref_user->function_id] = $_observation;
  }

  // Ajout de l'observation d'entrée si besoin
  $obs_entree = $_sejour->loadRefObsEntree();
  if ($obs_entree->_id) {
    $obs_entree->loadRefPraticien()->loadRefFunction();
    if ($obs_entree->_datetime >= $datetime_delta && $obs_entree->_datetime <= $datetime_avg) {
      $obs_entree->date = $obs_entree->_datetime;
      if (isset($patients_offline[$patient->_guid]["observations"][$obs_entree->_ref_praticien->function_id])) {
        $obs = $patients_offline[$patient->_guid]["observations"][$obs_entree->_ref_praticien->function_id];
        if ($obs_entree->_datetime > $obs->date) {
          $patients_offline[$patient->_guid]["observations"][$obs_entree->_ref_praticien->function_id] = $obs_entree;
        }
      }
      else {
        $patients_offline[$patient->_guid]["observations"][$obs_entree->_ref_praticien->function_id] = $obs_entree;
      }
    }
  }

  CMbArray::pluckSort($patients_offline[$patient->_guid]["observations"], SORT_DESC, "date");

  // Consultations
  $patients_offline[$patient->_guid]["consultations"] = array();

  foreach ($consultations as $_consultation) {
    if ($_consultation->sejour_id != $_sejour->_id) {
      continue;
    }
    if ($_consultation->type == "entree") {
      continue;
    }
    $_consultation->loadRefPraticien()->loadRefFunction();

    $patients_offline[$patient->_guid]["consultations"][$_consultation->_ref_chir->function_id] = $_consultation;
  }

  CMbArray::pluckSort($patients_offline[$patient->_guid]["consultations"], SORT_DESC, "_datetime");

  // Constantes
  $patients_offline[$patient->_guid]["constantes"] = "";
  $cstes                                           = array_reverse($_sejour->loadListConstantesMedicales($where_cste));
  if (count($cstes)) {
    CStoredObject::massLoadFwdRef($cstes, "user_id");
    foreach ($cstes as $_cste) {
      $_cste->loadRefUser();
    }
    $smarty_cstes->assign(
      "constantes_medicales_grid",
      CConstantesMedicales::buildGrid($cstes, false, true, CConstantesMedicales::guessHost($_sejour))
    );
    $smarty_cstes->assign("sejour", $_sejour);
    $patients_offline[$patient->_guid]["constantes"] = $smarty_cstes->fetch("print_constantes.tpl", '', '', 0);
  }

  // Plan de soins
  $page_break = 0;
  if (count($patients_offline[$patient->_guid]["transmissions"]) ||
    count($patients_offline[$patient->_guid]["observations"]) ||
    count($patients_offline[$patient->_guid]["consultations"]) ||
    $patients_offline[$patient->_guid]["constantes"]) {
    $page_break = 1;
  }
  $params = array(
    "sejours_ids"  => $_sejour->_id,
    "date"         => $date,
    "hours_before" => "2",
    "hours_after"  => "2",
    "empty_lines"  => "2",
    "dialog"       => 1,
    "mode_lite"    => 1,
    "page_break"   => $page_break
  );

  if ($plan_soins_active) {
    $patients_offline[$patient->_guid]["plan_soins"] = CApp::fetch("planSoins", "offline_plan_soins", $params);
    // Pour IE9 qui a des soucis avec les espaces entre une fermeture et une ouverture de td
    $patients_offline[$patient->_guid]["plan_soins"] = preg_replace('/>\s+<(t[dh])/mi', "><\\1", $patients_offline[$patient->_guid]["plan_soins"]);
  }
}

// Chargements des affectations a posteriori
CSejour::massLoadSurrAffectation($sejours, $datetime_avg);
CSejour::massLoadCurrAffectation($sejours, $datetime_avg, $service_id);

if ($service_id !== "urgence") {
  if ($service_id === "NP") {
    CMbArray::pluckSort($patients_offline, SORT_ASC, "sejour", "_ref_patient", "nom");
  }
  else {
    CMbArray::pluckSort($patients_offline, SORT_ASC, "sejour", "_ref_curr_affectation", "_ref_lit", "_view");
  }
}

$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("service", $service);
$smarty->assign("service_id", $service_id);
$smarty->assign("patients_offline", $patients_offline);
$smarty->assign("plan_soins_active", $plan_soins_active);

$smarty->display("offline_sejours_lite");
