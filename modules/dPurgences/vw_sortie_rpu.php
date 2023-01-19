<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();

// Type d'affichage
$view_sortie     = CView::post("view_sortie", "str default|tous", true);
$service_id      = CView::post("service_id", "ref class|CService", true);
$_responsable_id = CView::post("_responsable_id", "ref class|CMediusers");
$date            = CView::post("date", "date default|now", true);

CView::checkin();

// Chargement des urgences prises en charge
$ljoin                 = array();
$ljoin["rpu"]          = "sejour.sejour_id = rpu.sejour_id";
$ljoin["consultation"] = "consultation.sejour_id = sejour.sejour_id";

// Selection de la date
$date                   = CValue::getOrSession("date", CMbDT::date());
$date_tolerance         = CAppUI::conf("dPurgences date_tolerance");
$date_before            = CMbDT::date("-$date_tolerance DAY", $date);
$date_after             = CMbDT::date("+1 DAY", $date);
$where                  = array();
$group                  = CGroups::loadCurrent();
$where["group_id"]      = " = '$group->_id'";
$where["sejour.annule"] = " = '0'";
$where[]                = "sejour.entree BETWEEN '$date' AND '$date_after' 
  OR (sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$date_before' AND '$date_after')";

// RPU Existants
$where["rpu.rpu_id"] = "IS NOT NULL";

switch ($view_sortie) {
  case "tous":
    break;
  case "sortie":
    $where["sortie_reelle"]          = "IS NULL";
    $where["rpu.mutation_sejour_id"] = "IS NULL";
    break;
  case "normal":
  case "mutation":
  case "transfert":
  case "deces":
    $where["sortie_reelle"] = "IS NOT NULL";
    $where["mode_sortie"]   = "= '$view_sortie'";
}

$sejour = new CSejour();

/** @var CSejour[] $listSejours */
//Nous affichons d'abord les patients ayant une autorisation de sortie, puis ceux non sortie et sans autorisation, enfin ceux sortis
$order = "sejour.sortie_reelle ASC, rpu.date_sortie_aut ASC, consultation.heure ASC";
$listSejours = $sejour->loadList($where, $order, null, "sejour.sejour_id", $ljoin);
$patients    = CStoredObject::massLoadFwdRef($listSejours, "patient_id");
$prats       = CStoredObject::massLoadFwdRef($listSejours, "praticien_id");
CStoredObject::massLoadFwdRef($prats, "function_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CSejour::massLoadNDA($listSejours);
CPatient::massLoadIPP($patients);
CStoredObject::massLoadBackRefs($listSejours, "rpu");

$sejours_to_order = array("no_date_sortie_aut" => array(), "sortie_reelle" => array());
foreach ($listSejours as $sejour_id => $_sejour) {
  $_sejour->loadRefCurrAffectation()->loadRefService();
  if ($service_id) {
    $curr_aff = $_sejour->_ref_curr_affectation;
    if ((!$curr_aff->_id && (!$_sejour->service_id || $_sejour->service_id != $service_id)) || $curr_aff->service_id != $service_id) {
      unset($listSejours[$sejour_id]);
      continue;
    }
  }
  if ($_responsable_id && $_responsable_id != $_sejour->praticien_id) {
    unset($listSejours[$sejour_id]);
    continue;
  }
  $_sejour->loadRefsFwd();
  $_sejour->loadRefRPU();
  $_sejour->loadRefsConsultations();
  $_sejour->loadRefPrescriptionSejour();
  $_sejour->_ref_patient->updateBMRBHReStatus($_sejour);
  $_sejour->_veille = CMbDT::date($_sejour->entree) != $date;

  // Détail du RPU
  $rpu = $_sejour->_ref_rpu;
  $rpu->loadRefConsult();
  $rpu->loadRefMotifSFMU();
  $rpu->loadRefSejourMutation();
  $sejour_mutation = $rpu->_ref_sejour_mutation;
  $sejour_mutation->loadRefsAffectations();
  $sejour_mutation->loadRefsConsultations();
  $_nb_acte_sejour_rpu = 0;
  $valide              = true;
  foreach ($sejour_mutation->_ref_consultations as $consult) {
    $consult->countActes();
    $_nb_acte_sejour_rpu += $consult->_count_actes;
    if (!$consult->valide) {
      $valide = false;
    }
  }
  $rpu->_ref_consult->valide     = $valide;
  $sejour_mutation->_count_actes = $_nb_acte_sejour_rpu;

  foreach ($sejour_mutation->_ref_affectations as $_affectation) {
    if ($_affectation->loadRefService()->urgence) {
      unset($sejour_mutation->_ref_affectations[$_affectation->_id]);
      continue;
    }

    $_affectation->loadView();
  }
  $rpu->_ref_consult->countActes();

  //Réordonnancement des séjours pour mettre en premier les patients ayant une sortie autorisee, puis ceux sans prise en charge
  // Puis ceux sortis
  if (!$rpu->date_sortie_aut) {
    unset($listSejours[$sejour_id]);
    $sejours_to_order["no_date_sortie_aut"][$sejour_id] = $_sejour;
  }
  elseif ($_sejour->sortie_reelle) {
    unset($listSejours[$sejour_id]);
    $sejours_to_order["sortie_reelle"][$sejour_id] = $_sejour;
  }
}

foreach ($sejours_to_order as $_sejours_to_add) {
  foreach ($_sejours_to_add as $_sejour_to_add) {
    $listSejours[$_sejour_to_add->_id] = $_sejour_to_add;
  }
}

// Chargement des services
$where              = array();
$where["cancelled"] = "= '0'";
$service            = new CService();
$services           = $service->loadGroupList($where);

// Praticiens urgentistes
$group = CGroups::loadCurrent();

$listPrats = CAppUI::$user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);

// Si accès au module PMSI : peut modifier le diagnostic principal
$access_pmsi = 0;
if (CModule::exists("dPpmsi")) {
  $module           = new CModule();
  $module->mod_name = "dPpmsi";
  $module->loadMatchingObject();
  $access_pmsi = $module->getPerm(PERM_EDIT);
}

// Si praticien : peut modifier le CCMU, GEMSA et diagnostic principal
$is_praticien = CMediusers::get()->isPraticien();

CPrescription::massLoadLinesElementImportant(
  array_combine(
    CMbArray::pluck($listSejours, "_ref_prescription_sejour", "_id"),
    CMbArray::pluck($listSejours, "_ref_prescription_sejour")
  )
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("services_urg", CService::loadServicesUrgence());
$smarty->assign("services", $services);
$smarty->assign("service_id", $service_id);
$smarty->assign("_responsable_id", $_responsable_id);
$smarty->assign("listSejours", $listSejours);
$smarty->assign("view_sortie", $view_sortie);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("listResps", $prats);
$smarty->assign("date", $date);
$smarty->assign("access_pmsi", $access_pmsi);
$smarty->assign("is_praticien", $is_praticien);

$smarty->display("vw_sortie_rpu");
