<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientHandicap;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CTypeAnesth;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

CCanDo::checkEdit();

// Liste des Etablissements selon Permissions
$etablissements = CMediusers::loadEtablissements(PERM_READ);

// Chargement des prestations
$prestations = CPrestation::loadCurrentList();

$operation_id       = CView::get("operation_id", "ref class|COperation", true);
$chir_id            = CView::get("chir_id", "ref class|CMediusers", CAppUI::conf("dPplanningOp COperation use_session_praticien"));
$sejour_id          = CView::get("sejour_id", "ref class|CSejour");
$patient_id         = CView::get("pat_id", "ref class|CPatient");
$consult_related_id = CView::get("consult_related_id", "ref class|CConsultation");
$protocole_id       = CView::get('protocole_id', "ref class|CProtocole");
$contextual_call    = CView::get("contextual_call", "bool default|0");
$ext_cabinet_id     = CView::get("cabinet_id", "num", true);
$ext_patient_id     = CView::get("ext_patient_id", "num", true);
$ext_patient_nom    = CView::get("ext_patient_nom", "str", true);
$ext_patient_prenom = CView::get("ext_patient_prenom", "str", true);
$ext_patient_naissance = CView::get("ext_patient_naissance", "date", true);

CAccessMedicalData::logAccess("CConsultation-$consult_related_id");

// L'utilisateur est-il un praticien
$user = CMediusers::get();
if ($user->isPraticien() and !$chir_id) {
  $chir_id = $user->_id;
}

// Chargement du praticien
$chir = new CMediusers();
if ($chir_id) {
  $testChir = new CMediusers();
  $testChir->load($chir_id);
  if ($testChir->isPraticien()) {
    $chir = $testChir;
  }
}
$chir->loadRefFunction();
$prat = $chir;

$group = CGroups::loadCurrent();

// Chargement du patient
$patient = new CPatient();
if ($patient_id && !$operation_id && !$sejour_id) {
  $patient->load($patient_id);
  $patient->loadRefsSejours(array("sejour.group_id" => "= '$group->_id'"));
}

// On récupère le séjour
$sejour = new CSejour();

if ($sejour_id && !$operation_id) {
  $sejour->load($sejour_id);
  CAccessMedicalData::checkForSejour($sejour);
  $sejour->loadRefsFwd();
  $sejour->loadRefCurrAffectation()->loadRefService();
  $sejour->_ref_curr_affectation->updateView();
  if (!$chir_id) {
    $chir = $sejour->_ref_praticien;
  }
  // On ne change a priori pas le praticien du séjour
  $sejour->_ref_praticien->canDo();
  $prat    = $sejour->_ref_praticien;
  $patient = $sejour->_ref_patient;
}

// Liste des types d'anesthésie
$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

// Liste des anesthésistes
$anesthesistes = $user->loadAnesthesistes(PERM_READ);

// On récupère l'opération
$op = new COperation();
$op->load($operation_id);

if ($op->_id && $op->protocole_id) {
    $op->loadRefProtocole()->loadRefsProtocolesOp();
    $op->_ref_protocole->loadRefChir();
}

CAccessMedicalData::logAccess($op);

if ($op->_id) {

  $op->loadRefSejour();

  if (CBrisDeGlace::isBrisDeGlaceRequired() && !CAccessMedicalData::checkForSejour($op->_ref_sejour)) {
    CAppUI::accessDenied();
  }
  else {
    if (!$op->canDo()->read) {
      global $m, $tab;
      CAppUI::setMsg("Vous n'avez pas accés à cette intervention", UI_MSG_WARNING);
      CAppUI::redirect("m=$m&tab=$tab&operation_id=0");
    }
  }
  $op->loadRefPosition();
  $op->loadRefs();
  $op->loadRefsNotes();
  $op->_ref_chir->loadRefFunction();

  foreach ($op->_ref_actes_ccam as $acte) {
    $acte->loadRefExecutant();
  }

  $sejour =& $op->_ref_sejour;
  $sejour->loadRefsFwd();
  $sejour->loadRefCurrAffectation()->loadRefService();
  $sejour->_ref_praticien->canDo();
  $sejour->makeCancelAlerts($op->_id);
  $sejour->updateFieldsFacture();
  $chir =& $op->_ref_chir;
  $prat =& $sejour->_ref_praticien;

  $patient =& $sejour->_ref_patient;
}

if (!$op->_id) {
  $op->consult_related_id = $consult_related_id;
}

CView::setSession("chir_id", $chir->_id);
CView::checkin();

// Compléments de chargement du séjour
$sejour->makeDatesOperations();
$sejour->loadNDA();
$sejour->loadRefsNotes();
$sejour->loadRefPraticien();
$sejour->loadRefsAffectations();
$sejour->loadRefEtablissementProvenance();

// Chargements de chargement du patient
$patient->loadRefsSejours(array("sejour.group_id" => "= '$group->_id'"));
$patient->loadRefsFwd();
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient();
$patient->updateBMRBHReStatus();
$patient->_ref_medecin_traitant->getExercicePlaces();
$patient->loadRefsPatientHandicaps();

$correspondantsMedicaux = array();
if ($patient->_ref_medecin_traitant->_id) {
  $correspondantsMedicaux["traitant"] = $patient->_ref_medecin_traitant;
}
foreach ($patient->_ref_medecins_correspondants as $correspondant) {
  $correspondant->loadRefMedecin()->getExercicePlaces();
  $correspondantsMedicaux["correspondants"][] = $correspondant->_ref_medecin;
}

$medecin_adresse_par = "";
$sejour->loadRefAdresseParPraticien();
if ($sejour->adresse_par_prat_id && ($sejour->adresse_par_prat_id != $patient->_ref_medecin_traitant->_id)) {
  $sejour->_ref_adresse_par_prat->getExercicePlaces();
  $medecin_adresse_par = $sejour->_ref_adresse_par_prat;
}

// Chargement des etablissements externes
$etab = new CEtabExterne();
$count_etab_externe = $etab->countList();

$sejours = $patient->_ref_sejours;

if ($op->_id && !isset($sejours[$op->sejour_id])) {
  $sejours[$sejour->_id] = $sejour;
}

$config = CAppUI::conf("dPplanningOp CSejour");

$heure_sortie_ambu   = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_ambu', $group);
$heure_sortie_autre  = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_autre', $group);
$heure_entree_veille = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_veille', $group);
$heure_entree_jour   = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_jour', $group);

$list_hours_voulu = range(7, 20);
$list_minutes_voulu = range(0, 59, $config["min_intervalle"]);

foreach ($list_minutes_voulu as &$minute) {
  $minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
}

$config = CAppUI::conf("dPplanningOp COperation");
$hours_duree = array("deb" => $config["duree_deb"], "fin" =>$config["duree_fin"]);
$hours_urgence = array("deb" => $config["hour_urgence_deb"], "fin" => $config["hour_urgence_fin"]);
$mins_duree = $config["min_intervalle"];

// Récupération de la liste des services
$where = array();
$where["externe"]    = "= '0'";
$where["cancelled"]  = "= '0'";
$service = new CService();
$services = $service->loadGroupList($where);

foreach ($services as $_service) {
  $_service->loadRefUFSoins();
}

$sortie_sejour = CMbDT::dateTime();
if ($sejour->sortie_reelle) {
  $sortie_sejour = $sejour->sortie_reelle;
}

$where = array();
$where["entree"] = "<= '".$sortie_sejour."'";
$where["sortie"] = ">= '".$sortie_sejour."'";
$where["function_id"] = "IS NOT NULL";

$affectation = new CAffectation();
/** @var CAffectation[] $blocages_lit */
$blocages_lit = $affectation->loadList($where);

$where["function_id"] = "IS NULL";

foreach ($blocages_lit as $key => $blocage) {
  $blocage->loadRefLit()->loadRefChambre()->loadRefService();
  $where["lit_id"] = "= '$blocage->lit_id'";
  if (!$sejour->_id && $affectation->loadObject($where)) {
    $affectation->loadRefSejour();
    $affectation->_ref_sejour->loadRefPatient();
    $blocage->_ref_lit->_view .= " indisponible jusqu'à ".CMbDT::transform($affectation->sortie, null, "%Hh%Mmin %d-%m-%Y")." (".$affectation->_ref_sejour->_ref_patient->_view.")";
  }
}

if (CModule::getActive("maternite")) {
  $sejour->loadRefGrossesse();
}

$exchange_source = CExchangeSource::get("mediuser-" . CAppUI::$user->_id, CSourceSMTP::TYPE);

if (CAppUI::conf("dPplanningOp COperation use_poste")) {
  $op->loadRefPoste();
}
$op->loadRefChir2();
$op->loadRefChir3();
$op->loadRefChir4();

$_functions = array();

if ($chir->_id) {
  $_functions = $chir->loadBackRefs("secondary_functions");
}

if (!$op->_time_op && ! $op->temp_operation) {
  $op->_time_op = $op->temp_operation = "00:00:00";
}

$list_mode_sortie = array();
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
  $mode_sortie = new CModeSortieSejour();
  $where = array(
    "actif" => "= '1'",
  );
  $list_mode_sortie = $mode_sortie->loadGroupList($where);
}

$protocole = new CProtocole();
if ($protocole_id) {
  $protocole->load($protocole_id);
  $protocole->loadRefChir();
}

if (CModule::getActive("appFineClient")) {
  CAppFineClient::loadIdex($patient, CGroups::loadCurrent()->_id);
  $patient->loadRefStatusPatientUser();
}

$ext_patient = new CPatient();
if ($ext_patient_id && $ext_cabinet_id) {
  $ext_patient = CIdSante400::getMatch("CPatient", "ext_patient_id-$ext_cabinet_id", $ext_patient_id)->loadTargetObject();
}

if (!$ext_patient->_id) {
  $ext_patient->nom = $ext_patient_nom;
  $ext_patient->prenom = $ext_patient_prenom;
  $ext_patient->naissance = $ext_patient_naissance;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("contextual_call", (bool)$contextual_call);
$smarty->assign("ext_cabinet_id", $ext_cabinet_id);
$smarty->assign("ext_patient_id", $ext_patient_id);
$smarty->assign("ext_patient", $ext_patient);

$smarty->assign("sejours_collision", $patient->getSejoursCollisions());

$smarty->assign("canSante400", CModule::getCanDo("dPsante400"));

$smarty->assign("urgInstalled", CModule::getInstalled("dPurgences"));
$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("heure_sortie_ambu",   $heure_sortie_ambu);
$smarty->assign("heure_sortie_autre",  $heure_sortie_autre);
$smarty->assign("heure_entree_veille", $heure_entree_veille);
$smarty->assign("heure_entree_jour",   $heure_entree_jour);

$smarty->assign("op", $op);
$smarty->assign("plage", $op->plageop_id ? $op->_ref_plageop : new CPlageOp());
$smarty->assign("sejour", $sejour);
$smarty->assign("chir", $chir);
$smarty->assign("praticien", $prat);
$smarty->assign("patient", $patient);
$smarty->assign("patient_handicap_list", (new CPatientHandicap())->_specs["handicap"]->_list);
$smarty->assign("sejours", $sejours);
$smarty->assign("modurgence", 0);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("cpi_list", CChargePriceIndicator::getList());
$smarty->assign("_functions", $_functions);

$smarty->assign("listServices"  , $services);
$smarty->assign("etablissements", $etablissements);

$smarty->assign("hours_duree"  , $hours_duree);
$smarty->assign("hours_urgence", $hours_urgence);
$smarty->assign("mins_duree"   , $mins_duree);

$smarty->assign("list_hours_voulu"  , $list_hours_voulu);
$smarty->assign("list_minutes_voulu", $list_minutes_voulu);

$smarty->assign("prestations", $prestations);

$smarty->assign("correspondantsMedicaux", $correspondantsMedicaux);
$smarty->assign("count_etab_externe"    , $count_etab_externe);
$smarty->assign("listAnesthType"        , $listAnesthType);
$smarty->assign("anesthesistes"         , $anesthesistes);
$smarty->assign("medecin_adresse_par"   , $medecin_adresse_par);
$smarty->assign("blocages_lit"          , $blocages_lit);
$smarty->assign("list_mode_sortie"      , $list_mode_sortie);

$smarty->assign("exchange_source"       , $exchange_source);
$smarty->assign('protocole', $protocole);

$smarty->display("vw_edit_planning");
