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
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CEtabExterne;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientHandicap;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CIntervHorsPlage;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanningOp\CTypeAnesth;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

global $can, $m, $tab;

if (CAppUI::pref('create_dhe_with_read_rights')) {
  CCanDo::checkRead();
}
else {
  CCanDo::checkEdit();
}

$hors_plage = new CIntervHorsPlage();
if (!$hors_plage->canRead()) {
  $can->denied();
}

// Toutes les salles des blocs
$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, true, "nom", array("actif" => "= '1'"), array("actif" => "= '1'"));

// Les salles autorisées
$salle = new CSalle();
$listSalles = $salle->loadListWithPerms(PERM_READ, array("actif" => "= '1'"));


// Liste des Etablissements selon Permissions
$etablissements = CMediusers::loadEtablissements(PERM_READ);

// Chargement des prestations
$prestations = CPrestation::loadCurrentList();

$operation_id       = CValue::getOrSession("operation_id");
$chir_id            = CAppUI::conf("dPplanningOp COperation use_session_praticien")
  ? CValue::getOrSession("chir_id") : CValue::get("chir_id");
$sejour_id          = CValue::get("sejour_id");
$hour_urgence       = CValue::get("hour_urgence");
$min_urgence        = CValue::get("min_urgence");
$date_urgence       = CValue::get("date_urgence");
$salle_id           = CValue::get("salle_id");
$patient_id         = CValue::get("pat_id");
$grossesse_id       = CValue::get("grossesse_id");
$consult_related_id = CValue::get("consult_related_id");
$protocole_id       = CValue::get('protocole_id');
$salle_id           = CValue::get('salle_id');

// L'utilisateur est-il un praticien
$user = $chir = CMediusers::get();
if ($chir->isPraticien() and !$chir_id) {
  $chir_id = $chir->user_id;
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

$grossesse = new CGrossesse();
if ($grossesse_id && !$sejour_id && !$operation_id) {
  $grossesse->load($grossesse_id);
  $sejour->grossesse_id = $grossesse->_id;
  $sejour->_ref_grossesse = $grossesse;
  $patient = $grossesse->loadRefParturiente();
}

// On récupère l'opération
$op = new COperation();
$op->load($operation_id);

if ($op->_id) {
  $op->loadRefSejour();
  if (CAppUI::conf("dPplanningOp COperation use_session_praticien")) {
    CValue::setSession("chir_id", $op->chir_id);
  }

  if (CBrisDeGlace::isBrisDeGlaceRequired() && !CAccessMedicalData::checkForSejour($op->_ref_sejour)) {
    CAppUI::accessDenied();
  }
  else {
     if (!$op->canDo()->read) {
       global $m, $tab;
       CAppUI::setMsg("Vous n'avez pas accés à cette intervention hors plage", UI_MSG_WARNING);
       CAppUI::redirect("m=$m&tab=$tab&operation_id=0");
       CAppUI::accessDenied();
     }
  }

  // Chargement des régérences
  $op->loadRefs();
  $op->loadRefsNotes();
  $op->_ref_chir->loadRefFunction();

  $op->loadRefs();
  foreach ($op->_ref_actes_ccam as $acte) {
    $acte->loadRefExecutant();
  }
  
  $sejour = $op->_ref_sejour;
  $sejour->loadRefsFwd();
  $sejour->loadRefCurrAffectation()->loadRefService();
  $sejour->_ref_curr_affectation->updateView();
  $sejour->_ref_praticien->canDo();
  $sejour->makeCancelAlerts($op->_id);
  $sejour->updateFieldsFacture();
  $chir    = $op->_ref_chir;
  $patient = $sejour->_ref_patient;
  $prat    = $sejour->_ref_praticien;
}
else {
  if ($hour_urgence && isset($min_urgence)) {
    $hour = intval(substr($hour_urgence, 0, 2));
    $min = intval(substr($min_urgence, 0, 2));
    $op->_time_urgence = "$hour:$min:00";
  }

  $op->consult_related_id = $consult_related_id;
  $op->date = $op->_datetime = $date_urgence ? $date_urgence : CMbDT::date();
  $op->salle_id = $salle_id;
}
// Liste des types d'anesthésie
$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

// Liste des anesthésistes
$anesthesistes = $user->loadAnesthesistes(PERM_READ);

// Compléments de chargement du séjour
$sejour->makeDatesOperations();
$sejour->loadNDA();
$sejour->loadRefsNotes();
$sejour->loadRefsAffectations();
$sejour->loadRefEtablissementProvenance();

if (CModule::getActive("maternite")) {
  $sejour->loadRefGrossesse();
}

// Chargements de chargement du patient
$patient->loadRefsSejours(array("sejour.group_id" => "= '$group->_id'"));
$patient->loadRefsFwd();
$patient->loadRefsCorrespondants();
$patient->loadRefsCorrespondantsPatient();
$patient->updateBMRBHReStatus($sejour);
$patient->_ref_medecin_traitant->getExercicePlaces();

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
$hours = range($config["heure_deb"], $config["heure_fin"]);
$mins = range(0, 59, $config["min_intervalle"]);

$config = CAppUI::conf("dPplanningOp COperation");
$hours_duree = array("deb" => $config["duree_deb"], "fin" =>$config["duree_fin"]);
$hours_urgence = array("deb" => $config["hour_urgence_deb"], "fin" => $config["hour_urgence_fin"]);
$mins_duree = $config["min_intervalle"];

$heure_sortie_ambu   = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_ambu', $group);
$heure_sortie_autre  = CAppUI::conf('dPplanningOp CSejour default_hours heure_sortie_autre', $group);
$heure_entree_veille = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_veille', $group);
$heure_entree_jour   = CAppUI::conf('dPplanningOp CSejour default_hours heure_entree_jour', $group);

// Récupération de la liste des services
$where = array();
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";
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

$affectatione = new CAffectation();
/** @var CAffectation[] $blocages_lit */
$blocages_lit = $affectatione->loadList($where);

$where["function_id"] = "IS NULL";

foreach ($blocages_lit as $key => $blocage) {
  $blocage->loadRefLit()->loadRefChambre()->loadRefService();
  $where["lit_id"] = "= '$blocage->lit_id'";
  if (!$sejour->_id && $affectatione->loadObject($where)) {
    $affectatione->loadRefSejour();
    $affectatione->_ref_sejour->loadRefPatient();
    $jusqua = CMbDT::transform($affectatione->sortie, null, "%Hh%Mmin %d-%m-%Y")." (".$affectatione->_ref_sejour->_ref_patient->_view;
    $blocage->_ref_lit->_view .= " indisponible jusqu'à ".$jusqua.")";
  }
}

$exchange_source = CExchangeSource::get("mediuser-" . CAppUI::$user->_id, CSourceSMTP::TYPE);

$_functions = array();

if ($chir->_id) {
  $_functions = $chir->loadBackRefs("secondary_functions");
}

$op->loadRefChir2();
$op->loadRefChir3();
$op->loadRefChir4();
$op->loadRefPosition();

if (!$op->_id) {
  $op->_time_op = $op->temp_operation = "00:00:00";
  if ($hour_urgence != "" && $min_urgence != "") {
    $time = "$hour_urgence:$min_urgence:00";
  }
  else {
    $time_config = str_pad($hours_urgence["deb"], 2, "0", STR_PAD_LEFT).":00:00";

    $time = CMbDT::transform(CMbDT::time(), null , "%H:00:00");

    if ($time < $time_config) {
      $time = $time_config;
    }
  }
  $op->_time_urgence = $op->time_operation = $time;
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

if ($salle_id) {
  $op->salle_id = $salle_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours_collision", $patient->getSejoursCollisions());
$smarty->assign("isPrescriptionInstalled", CModule::getActive("dPprescription"));
$smarty->assign("canSante400", CModule::getCanDo("dPsante400"));
$smarty->assign("urgInstalled", CModule::getInstalled("dPurgences"));
$smarty->assign("heure_sortie_ambu"   , $heure_sortie_ambu);
$smarty->assign("heure_sortie_autre"  , $heure_sortie_autre);
$smarty->assign("heure_entree_veille" , $heure_entree_veille);
$smarty->assign("heure_entree_jour"   , $heure_entree_jour);

$smarty->assign("op"        , $op);
$smarty->assign("plage"     , $op->plageop_id ? $op->_ref_plageop : new CPlageOp );
$smarty->assign("sejour"    , $sejour);
$smarty->assign("chir"      , $chir);
$smarty->assign("praticien" , $prat);
$smarty->assign("patient"   , $patient );
$smarty->assign("sejours"   , $sejours);
$smarty->assign("ufs"       , CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("cpi_list"  , CChargePriceIndicator::getList());
$smarty->assign("_functions", $_functions);
$smarty->assign("patient_handicap_list", (new CPatientHandicap())->_specs["handicap"]->_list);

$smarty->assign("modurgence", 1);
$smarty->assign("date_min", CMbDT::date());
$smarty->assign("date_max", CMbDT::date("+".CAppUI::conf("dPplanningOp COperation nb_jours_urgence")." days", CMbDT::date()));

$smarty->assign("listAnesthType", $listAnesthType);
$smarty->assign("anesthesistes" , $anesthesistes);
$smarty->assign("listServices"  , $services);
$smarty->assign("etablissements", $etablissements);

$smarty->assign("hours"        , $hours);
$smarty->assign("mins"         , $mins);
$smarty->assign("hours_duree"  , $hours_duree);
$smarty->assign("hours_urgence", $hours_urgence);
$smarty->assign("mins_duree"   , $mins_duree);

$smarty->assign("prestations", $prestations);
$smarty->assign("blocages_lit", $blocages_lit);

$smarty->assign("correspondantsMedicaux", $correspondantsMedicaux);
$smarty->assign("count_etab_externe", $count_etab_externe);
$smarty->assign("medecin_adresse_par", $medecin_adresse_par);

$smarty->assign("listBlocs",  $listBlocs);

$smarty->assign("list_mode_sortie"      , $list_mode_sortie);

$smarty->assign("exchange_source"       , $exchange_source);
$smarty->assign('protocole', $protocole);

$smarty->display("vw_edit_planning");
