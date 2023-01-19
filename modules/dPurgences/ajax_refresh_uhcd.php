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
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Imeds\CImeds;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();

// Type d'affichage
$uhcd_affichage = CView::post("uhcd_affichage", "str default|" . CAppUI::conf("dPurgences default_view"), true);

// Parametre de tri
$order_way = CView::get("order_way", "str default|" . CAppUI::pref("chooseSortRPU"), true);
$order_col = CView::get("order_col", "str default|" . CAppUI::pref("defaultRPUSort"), true);

// Selection de la date
$date           = CView::get("date", "date default|" . CMbDT::date(), true);
$date_tolerance = CAppUI::conf("dPurgences date_tolerance");
$date_before    = CMbDT::date("-$date_tolerance DAY", $date);
$date_after     = CMbDT::date("+1 DAY", $date);
CView::checkin();

// L'utilisateur doit-il voir les informations médicales
$user        = CMediusers::get();
$medicalView = $user->isMedical();

$group     = CGroups::loadCurrent();
$listPrats = $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);

$sejour                   = new CSejour;
$where                    = array();
$ljoin["rpu"]             = "sejour.sejour_id = rpu.sejour_id";
$ljoin["patients"]        = "sejour.patient_id = patients.patient_id";
$where[]                  = "sejour.entree BETWEEN '$date' AND '$date_after' 
  OR (sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$date_before' AND '$date_after' AND sejour.annule = '0')";
$where[]                  = CAppUI::pref("showMissingRPU") ?
  "sejour.type = 'comp' OR rpu.rpu_id IS NOT NULL" :
  "rpu.rpu_id IS NOT NULL";
$where["sejour.group_id"] = "= '$group->_id'";
$where["sejour.UHCD"]     = "= '1'";

if ($uhcd_affichage == "prendre_en_charge") {
  $ljoin["consultation"]                 = "consultation.sejour_id = sejour.sejour_id";
  $where["consultation.consultation_id"] = "IS NULL";
}

if ($uhcd_affichage == "presents") {
  $where["sejour.sortie_reelle"] = "IS NULL";
  $where["sejour.annule"]        = " = '0'";

  if (CAppUI::conf("dPurgences create_sejour_hospit")) {
    $where["rpu.mutation_sejour_id"] = "IS NULL";
  }
}

if ($uhcd_affichage == "annule") {
  $where["sejour.sortie_reelle"] = "IS NOT NULL";
}

if ($order_col != "_entree" && $order_col != "ccmu" && $order_col != "_patient_id") {
  $order_col = "ccmu";
}

if ($order_col == "_entree") {
  $order = "entree $order_way, rpu.ccmu $order_way";
}

if ($order_col == "ccmu") {
  $order = "rpu.ccmu $order_way, entree $order_way";
}

if ($order_col == "_patient_id") {
  $order = "patients.nom $order_way, ccmu $order_way";
}

/** @var CSejour[] $listSejours */
$listSejours = $sejour->loadList($where, $order, null, null, $ljoin);
foreach ($listSejours as $_sejour) {
  // Chargement du numero de dossier
  $_sejour->loadNDA();
  $_sejour->loadRefsFwd();
  $_sejour->loadRefRPU();
  $_sejour->_ref_rpu->loadRefSejourMutation();
  $_sejour->_ref_rpu->loadRefConsult();
  $_sejour->_ref_rpu->loadRefsLastAttentes();
  $_sejour->loadRefsConsultations();
  $_sejour->loadRefsNotes();
  $_sejour->countDocItems();
  $_sejour->loadRefPrescriptionSejour();

  $prescription = $_sejour->_ref_prescription_sejour;
  if ($prescription) {
    $prescription->loadRefsPrescriptionLineMixes();
    $prescription->loadRefsLinesMedByCat();
    $prescription->loadRefsLinesElementByCat();

    $_sejour->_ref_prescription_sejour->countRecentModif();
  }

  // Chargement de l'IPP
  $_sejour->_ref_patient->loadIPP();

  // Séjours antérieurs  
  $_sejour->_veille = CMbDT::date($_sejour->entree) != $date;

  // Ajout des documents de la consultation dans le compteur
  $consult_atu = $_sejour->_ref_consult_atu;

  if ($consult_atu->_id) {
    $_sejour->_nb_files      += $consult_atu->_nb_files;
    $_sejour->_nb_docs       += $consult_atu->_nb_docs;
    $_sejour->_nb_files_docs += $consult_atu->_nb_files + $consult_atu->_nb_docs;

    $consult_atu->loadRefsPrescriptions();

    if (isset($consult_atu->_ref_prescriptions["externe"])) {
      $_sejour->_nb_docs++;
      $_sejour->_nb_files_docs++;
    }
  }
}

CSejour::massLoadCurrAffectation($listSejours);
foreach ($listSejours as $key => $sejour) {
  $sejour->_ref_curr_affectation->loadRefPraticien();
}

CPrescription::massLoadLinesElementImportant(
  array_combine(
    CMbArray::pluck($listSejours, "_ref_prescription_sejour", "_id"),
    CMbArray::pluck($listSejours, "_ref_prescription_sejour")
  )
);

// Tri pour afficher les sans CCMU en premier
if ($order_col == "ccmu") {
  function ccmu_cmp($sejour1, $sejour2) {
    $ccmu1 = CValue::first($sejour1->_ref_rpu->ccmu, "9");
    $ccmu2 = CValue::first($sejour2->_ref_rpu->ccmu, "9");
    if ($ccmu1 == "P") {
      $ccmu1 = "1";
    }
    if ($ccmu2 == "P") {
      $ccmu2 = "1";
    }

    return $ccmu2 - $ccmu1;
  }

  uasort($listSejours, "ccmu_cmp");
}

// Chargement des boxes d'urgences
$boxes = array();
foreach (CService::loadServicesUHCD() as $service) {
  foreach ($service->_ref_chambres as $chambre) {
    foreach ($chambre->_ref_lits as $lit) {
      $boxes[$lit->_id] = $lit;
    }
  }
}

// Si admin sur le module urgences, alors modification autorisée du diagnostic
// infirmier depuis la main courante.
$module           = new CModule;
$module->mod_name = "dPurgences";
$module->loadMatchingObject();
$admin_urgences = $module->canAdmin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("boxes", $boxes);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("listSejours", $listSejours);
$smarty->assign("uhcd_affichage", $uhcd_affichage);
$smarty->assign("medicalView", $medicalView);
$smarty->assign("date", $date);
$smarty->assign("date_before", $date_before);
$smarty->assign("today", CMbDT::date());
$smarty->assign("isImedsInstalled", (CModule::getActive("dPImeds") && CImeds::getTagCIDC(CGroups::loadCurrent())));
$smarty->assign("admin_urgences", $admin_urgences);
$smarty->assign("type", "UHCD");
$smarty->display("inc_main_courante");
