<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

global $m;

CCanDo::checkRead();
// Plateaux disponibles
$spec_show_cancelled_services = array(
  "bool",
  "default" => CAppUI::conf("ssr recusation view_services_inactifs")
);
$show_cancelled_services      = CView::get("show_cancelled_services", $spec_show_cancelled_services, true);
$date                         = CView::get("date", "date default|now", true);
$order_way                    = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col                    = CView::get("order_col", "str default|patient_id", true);
$show                         = CView::get("show", "str default|all", true);
$group_by                     = CView::get("group_by", "bool");

// Filtre
$filter               = new CSejour();
$filter->service_id   = CView::get("service_id", "ref class|CService", true);
$filter->praticien_id = CView::get("praticien_id", "ref class|CMediusers", true);
$filter->referent_id  = CView::get("referent_id", "ref class|CMediusers", true);

CView::checkin();

// Chargement des sejours SSR pour la date selectionnée
$group     = CGroups::loadCurrent();
$group_id  = $group->_id;
$date_time = $date . " " . CMbDT::time();

$where = array(
  "type"            => "= '$m'",
  "sejour.group_id" => "= '$group_id'",
  "sejour.annule"   => "= '0'"
);

$ljoin = array(
  "patients" => "sejour.patient_id = patients.patient_id"
);

switch ($order_col) {
  default:
  case "patient_id":
    $order = "patients.nom $order_way, patients.prenom, sejour.entree";
    break;
  case "entree":
    $order = "sejour.entree $order_way, patients.nom, patients.prenom";
    break;
  case "sortie":
    $order = "sejour.sortie $order_way, patients.nom, patients.prenom";
    break;
  case "praticien_id":
    $order = "sejour.praticien_id $order_way, patients.nom, patients.prenom";
    break;
  case "libelle":
    $order = "sejour.libelle $order_way, patients.nom, patients.prenom";
    break;
  case "service_id":
    $order = "sejour.service_id $order_way, patients.nom, patients.prenom";
}

$sejours = CSejour::loadListForDate($date, $where, $order, null, null, $ljoin);

// Masquer les services inactifs
if (!$show_cancelled_services) {
  $service            = new CService();
  $service->group_id  = $group->_id;
  $service->cancelled = "1";
  $services_ids       = array_keys($service->loadMatchingList());

  foreach ($sejours as $_sejour) {
    if ($_sejour->service_id && in_array($_sejour->service_id, $services_ids)) {
      unset($sejours[$_sejour->_id]);
    }
  }
}

// Filtre sur les services
$services        = array();
$praticiens      = array();
$kines           = array();
$sejours_by_kine = array(
  // Séjours sans kinés
  "" => array(),
);

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadBackRefs($sejours, "bilan_ssr");
CSejour::massLoadCurrAffectation($sejours, $date_time);

// Filtres des séjours
foreach ($sejours as $_sejour) {
  // Filtre sur service
  $service_id = $_sejour->_ref_curr_affectation->service_id;
  if (!$service_id) {
    $service_id = $_sejour->service_id;
  }
  if (isset($services[$service_id])) {
    $service = $services[$service_id];
  }
  else {
    $service = new CService();
    $service->load($service_id);
    $services[$service->_id] = $service;
  }

  if ($filter->service_id && $service_id != $filter->service_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  // Filtre sur prescription, pas nécessairement actif
  $prescription = $_sejour->loadRefPrescriptionSejour();
  if ($show == "nopresc" && $prescription && $prescription->_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  // Filtre sur praticien
  $praticien                   = $_sejour->loadRefPraticien();
  $praticiens[$praticien->_id] = $praticien;
  if ($filter->praticien_id && $_sejour->praticien_id != $filter->praticien_id) {
    unset($sejours[$_sejour->_id]);
    continue;
  }

  // Bilan SSR
  $bilan = $_sejour->loadRefBilanSSR();

  // Kinés référent et journée
  $bilan->loadRefKineJournee($date);
  $kine_journee              = $bilan->_ref_kine_journee;
  $kines[$kine_journee->_id] = $kine_journee;

  $kine_referent = $bilan->_ref_kine_referent;
  if (!$kine_journee->_id) {
    $kines[$kine_referent->_id] = $kine_referent;
  }

  if ($filter->referent_id && $kine_referent->_id != $filter->referent_id && $kine_journee->_id != $filter->referent_id) {
    unset($kines[$kine_journee->_id]);
    if (!$kine_journee->_id) {
      unset($kines[$kine_referent->_id]);
    }
    unset($sejours[$_sejour->_id]);
    continue;
  }
}

// Chargement du détail des séjour
CStoredObject::massLoadBackRefs($sejours, "notes");
/** @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

$alert_handler = HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler');

foreach ($sejours as $_sejour) {
  $kine_journee  = $_sejour->_ref_bilan_ssr->_ref_kine_journee;
  $kine_referent = $_sejour->_ref_bilan_ssr->_ref_kine_referent;
  // Regroupement par kine
  $sejours_by_kine[$kine_referent->_id][] = $_sejour;
  if ($kine_journee->_id && $kine_journee->_id != $kine_referent->_id) {
    $sejours_by_kine[$kine_journee->_id][] = $_sejour;
  }

  // Détail du séjour
  $_sejour->checkDaysRelative($date);
  $_sejour->loadRefsNotes();

  // Patient
  $_sejour->loadRefPatient();

  $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);

  // Modification des prescription
  if ($prescription = $_sejour->_ref_prescription_sejour) {
    if (!$alert_handler) {
      $prescription->countFastRecentModif();
    }
  }

  // Praticien demandeur
  $bilan = $_sejour->_ref_bilan_ssr;
  $bilan->loadRefPraticienDemandeur();
}

if ($alert_handler) {
  CPrescription::massCountAlertsNotHandled(CMbArray::pluck($sejours, "_ref_prescription_sejour"));
}

if ($order_col == "lit_id") {
  $keys   = array_keys($sejours);
  $sorter = array_map("strtolower", CMbArray::pluck($sejours, "_ref_curr_affectation", "_ref_lit", "_view"));
  array_multisort($sorter, SORT_STRING | ($order_way === "ASC" ? SORT_ASC : SORT_DESC), $sejours, $keys);
  $sejours = array_combine($keys, $sejours);
}

// Ajustements services
$service = new CService();
$service->load($filter->service_id);
$services[$service->_id] = $service;
unset($services[""]);

// Ajustements kinés
$kine = CMediusers::get($filter->referent_id);
$kine->loadRefFunction();
$kines[$kine->_id] = $kine;
unset($kines[""]);

// Tris a posteriori : détruit les clés !
$order_kine = CMbArray::pluck($kines, "_view");
array_multisort($order_kine, SORT_ASC, $kines);

$order_service = CMbArray::pluck($services, "_view");
array_multisort($order_service, SORT_ASC, $services);

$order_prat = CMbArray::pluck($praticiens, "_view");
array_multisort($order_prat, SORT_ASC, $praticiens);

// Couleurs
$libelles = CMbArray::pluck($sejours, "libelle");
$colors   = CColorLibelleSejour::loadAllFor($libelles);

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("date", $date);
$smarty->assign("filter", $filter);
$smarty->assign("colors", $colors);
$smarty->assign("sejours", $sejours);

$smarty->assign("sejours_by_kine", $sejours_by_kine);
$smarty->assign("kines", $kines);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("services", $services);
$smarty->assign("show", $show);
$smarty->assign("group_by", $group_by);
$smarty->assign("show_cancelled_services", $show_cancelled_services);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);

$smarty->display("vw_sejours_ssr");
