<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamComp;
use Ox\Mediboard\Cabinet\CTechniqueComp;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkEdit();

$user = CMediusers::get();

$date         = CView::get("date", "date default|now", true);
$vue          = CView::get("vue2", "bool default|".CAppUI::pref("AFFCONSULT", 0), true);
$prat_id      = CView::get("chirSel", "ref class|CMediusers default|".$user->_id, true);

$selConsult   = CView::get("selConsult", "ref class|CConsultation", true);
$dossier_anesth_id = CView::get("dossier_anesth_id", "ref class|CConsultAnesth", true);
$represcription = CView::get("represcription", "bool default|0");
$synthese_rpu   = CView::get("synthese_rpu", "bool default|0");
$launchTeleconsultation = CView::get("launchTeleconsultation", "bool default|0");

$today = CMbDT::date();
$hour  = CMbDT::time();
$now   = CMbDT::dateTime();

if (!isset($current_m)) {
  global $m;
  $current_m = CView::get("current_m", "str default|$m");
}

if (isset($_GET["date"])) {
  $selConsult = null;
  CView::setSession("selConsult");
}

// Test compliqué afin de savoir quelle consultation charger
$consult = new CConsultation();
$consult->load($selConsult);

CAccessMedicalData::logAccess($consult);

if (isset($_GET["selConsult"])) {
  if ($consult->_id && $consult->patient_id) {
    $consult->loadRefPlageConsult();
    $prat_id = $consult->_ref_plageconsult->chir_id;
    CView::setSession("chirSel", $prat_id);
  }
  else {
    $consult = new CConsultation();
    $selConsult = null;
    CView::setSession("selConsult");
  }
}
else {
  if ($consult->_id && $consult->patient_id) {
    $consult->loadRefPlageConsult();
    if ($prat_id != $consult->_ref_plageconsult->chir_id) {
      $consult = new CConsultation();
      $selConsult = null;
      CView::setSession("selConsult");
    }
  }
}

CView::checkin();

// On charge le praticien
$userSel = new CMediusers();
$userSel->load($prat_id);
$userSel->loadRefFunction();
$canUserSel = $userSel->canDo();

if (!$consult->_id) {
  if ($current_m == "dPurgences") {
    CAppUI::setMsg("Vous devez selectionner une consultation", UI_MSG_ALERT);
    CAppUI::redirect("m=urgences&tab=0");
  }

  $smarty = new CSmartyDP("modules/dPcabinet");
  $smarty->assign("consult"  , $consult);
  $smarty->assign("current_m", $current_m);
  $smarty->assign("date"     , $date);
  $smarty->assign("vue"      , $vue);
  $smarty->assign("userSel"  , $userSel);
  $smarty->assign("launchTeleconsultation", $launchTeleconsultation);
  $smarty->display("edit_consultation");
  CApp::rip();
}

if (!$synthese_rpu) {
  $consult->canDo()->needsEdit(array("selConsult" => null));
}

switch ($current_m) {
  case "dPurgences":
    $group = CGroups::loadCurrent();
    $listPrats = $user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true);
    $ecg_tabs = $consult->sejour_id ? CFilesCategory::getEmergencyTabCategories($group) : [];
    break;
  default:
    $listPrats = CConsultation::loadPraticiens(PERM_EDIT);
    $ecg_tabs = [];
}

if (!$userSel->isMedical() && $current_m != "dPurgences") {
  CAppUI::setMsg("Vous devez selectionner un professionnel de santé", UI_MSG_ALERT);
  CAppUI::redirect("m=dPcabinet&tab=0");
}

$consultAnesth = null;

// Consultation courante
$consult->_ref_chir = $userSel;

// Chargement de la consultation
$patient = $consult->loadRefPatient();
$patient->loadExternalIdentifiers();

$patient->updateBMRBHReStatus($consult);

if (CModule::getActive("appFineClient")) {
  CAppFineClient::loadIdex($consult, $consult->loadRefGroup()->_id);
  $patient->loadRefStatusPatientUser();
}

if ($patient->_vip) {
  global $can;
  $can->denied();
}

$consultAnesth = $consult->loadRefConsultAnesth($dossier_anesth_id);
// Récupérer le dernier score de Cormack
if (!$consultAnesth->cormack) {
  $consultAnesth->getLastCormackValues($patient->_id);
}

$consultAnesth->loadPsaClairance();
$consultAnesth->loadRefOperation();

// Chargement du patient
$patient->countBackRefs("consultations");
$patient->countBackRefs("sejours");
$patient->countINS();

$patient->loadRefPhotoIdentite();
$patient->loadRefsNotes();
$patient->loadRefsCorrespondants();
$patient->loadRefLatestConstantes(null, [], null, false);
$patient->loadRefMedecinTraitant()->getExercicePlaces();

// Si medecin correspondant devient le medecin traitant, ne pas l'afficher 2 fois
foreach ($patient->_ref_medecins_correspondants as $_corresp) {
  if ($patient->_ref_medecin_traitant->_id == $_corresp->medecin_id) {
    unset($patient->_ref_medecins_correspondants[$_corresp->_id]);
  }
}

foreach ($patient->_ref_medecins_correspondants as $_medecin_correspondant) {
    $_medecin_correspondant->loadRefMedecin()->getExercicePlaces();
}

// Affecter la date de la consultation
$date = $consult->_ref_plageconsult->date;

// Tout utilisateur peut consulter en lecture seule une consultation de séjour
$consult->canDo();

if (CModule::getActive("fse")) {
  $fse = CFseFactory::createFSE();
  if ($fse) {
    $fse->loadIdsFSE($consult);
    $fse->makeFSE($consult);

    $cps = CFseFactory::createCPS()->loadIdCPS($consult->_ref_chir);

    CFseFactory::createCV()->loadIdVitale($consult->_ref_patient);
  }
}

$maternite_active = CModule::getActive("maternite");

if ($maternite_active && $consult->grossesse_id) {
  $patient->loadLastGrossesse();
  $consult->getSuiviGrossesse();
  $consult->getSA();
}

$consult->loadRefGrossesse();
$consult->loadListEtatsDents();
$consult->getCovidDiag();

$patient->loadRefDossierMedical();
$dossier_medical = $consult->_ref_patient->_ref_dossier_medical;
if ($dossier_medical->_id) {
  $dossier_medical->loadRefsAllergies();
  $dossier_medical->loadRefsAntecedents();
  $dossier_medical->countAntecedents(false);
  $dossier_medical->countAllergies();
}

$sejour = $consult->loadRefSejour();

$sejour->getCovidDiag();

// Chargement du sejour
$rpu = null;
if ($sejour->_id) {
  // Cas des urgences
  $rpu = $sejour->loadRefRPU();
    $rpu->loadRefSejour();
}

$isPrescriptionInstalled = CModule::getActive("dPprescription") && CPrescription::isMPMActive();
$consult->loadRefPraticien();

// Création du template
$smarty = new CSmartyDP("modules/dPcabinet");

$smarty->assign("consult"        , $consult);

$smarty->assign("isPrescriptionInstalled", $isPrescriptionInstalled);

$smarty->assign("listPrats"      , $listPrats);

$smarty->assign("launchTeleconsultation", $launchTeleconsultation);

if ($isPrescriptionInstalled) {
  $smarty->assign("line"         , new CPrescriptionLineMedicament());

  CPrescription::$_load_lite = true;
  $consult->_ref_sejour->loadRefPrescriptionSejour();
  $consultAnesth->loadRefSejour()->loadRefPrescriptionSejour();
  CPrescription::$_load_lite = false;
  $consult->_ref_sejour->_ref_prescription_sejour->loadLinesElementImportant();
}

$form_tabs = array();
if (CModule::getActive('forms')) {
  $objects = array(
    array("tab_examen", $consult),
  );

  if ($consultAnesth && $consultAnesth->_id) {
    $objects[] = array('tab_examen_anesth', $consultAnesth);
  }

  $form_tabs = CExClassEvent::getTabEvents($objects);
}

//Rcupration de la source labo
$source_labo = CExchangeSource::get(
    "OxLabo" . CGroups::loadCurrent()->_id,
    CSourceHTTP::TYPE,
    false,
    "OxLaboExchange",
    false
);

$smarty->assign('form_tabs', $form_tabs);
$smarty->assign("represcription" , $represcription);
$smarty->assign("date"           , $date);
$smarty->assign("hour"           , $hour);
$smarty->assign("vue"            , $vue);
$smarty->assign("today"          , $today);
$smarty->assign("now"            , $now);
$smarty->assign("_is_anesth"     , $consult->_is_anesth);
$smarty->assign("consult_anesth" , $consultAnesth);
$smarty->assign("_is_dentiste"   , $consult->_is_dentiste);
$smarty->assign("current_m"      , $current_m);
$smarty->assign("userSel"        , $userSel);
$smarty->assign("dossier_medical", $dossier_medical);
$smarty->assign("antecedents"    , $dossier_medical->_ref_antecedents_by_type);
$smarty->assign("rpu"            , $rpu);
$smarty->assign("synthese_rpu"   , $synthese_rpu);
$smarty->assign("tabs_count"     , CConsultation::makeTabsCount($consult, $dossier_medical, $consultAnesth, $sejour));
$smarty->assign('ecg_tabs'       , $ecg_tabs);
$smarty->assign("getSourceLabo", $source_labo->active ? true : false);

if ($maternite_active && $consult->grossesse_id) {
  $smarty->assign("constante", new CConstantesMedicales());
}

if (count($consult->_refs_dossiers_anesth)) {
  $secs = range(0, 60-1, 1);
  $mins = range(0, 15-1, 1);

  if ($maternite_active && $consult->grossesse_id && $consult->_ref_consult_anesth->_id
      && !$consult->_ref_consult_anesth->operation_id
  ) {
    $grossesse = $consult->loadRefGrossesse();
    $sejours = $grossesse->loadRefsSejours();

    if (count($sejours)) {
      $last_sejour = end($sejours);
      $consult->_ref_consult_anesth->sejour_id = $last_sejour->_id;
    }
  }

  $smarty->assign("secs"    , $secs);
  $smarty->assign("mins"    , $mins);
  $smarty->assign("examComp", new CExamComp());
  $smarty->assign("techniquesComp", new CTechniqueComp());
  $smarty->display("edit_consultation");
}
else {
  if (CAppUI::pref("MODCONSULT")) {
    $where = array();
    $where["entree"] = "<= '".CMbDT::dateTime()."'";
    $where["sortie"] = ">= '".CMbDT::dateTime()."'";
    $where["function_id"] = "IS NOT NULL";

    $affectation = new CAffectation();
    /** @var CAffectation[] $blocages_lit */
    $blocages_lit = $affectation->loadList($where);

    $where["function_id"] = "IS NULL";

    foreach ($blocages_lit as $blocage) {
      $blocage->loadRefLit()->loadRefChambre()->loadRefService();
      $where["lit_id"] = "= '$blocage->lit_id'";

      if ($affectation->loadObject($where)) {
        $sejour = $affectation->loadRefSejour();
        $patient = $sejour->loadRefPatient();
        $blocage->_ref_lit->_view .= " indisponible jusqu'à ".CMbDT::transform($affectation->sortie, null, "%Hh%Mmin %d-%m-%Y");
        $blocage->_ref_lit->_view .= " (".$patient->_view." (".strtoupper($patient->sexe).") ";
        $blocage->_ref_lit->_view .= CAppUI::conf("dPurgences age_patient_rpu_view") ? $patient->_age.")" : ")" ;
      }
    }
    $smarty->assign("blocages_lit" , $blocages_lit);
    $smarty->assign("consult_anesth", null);

    $smarty->display("edit_consultation");
  }
  else {
    $smarty->display("edit_consultation_classique");
  }
}
