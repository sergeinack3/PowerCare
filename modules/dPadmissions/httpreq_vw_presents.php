<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour = new CSejour();
$ds     = $sejour->getDS();

// Type d'admission
$services_ids       = CValue::getOrSession("services_ids");
$sejours_ids        = CValue::getOrSession("sejours_ids");
$prat_id            = CValue::getOrSession("prat_id");
$order_col          = CValue::getOrSession("order_col", "patient_id");
$order_way          = CValue::getOrSession("order_way", "ASC");
$date               = CValue::getOrSession("date", CMbDT::date());
$heure              = CValue::getOrSession("heure");
$next               = CMbDT::date("+1 DAY", $date);
$filterFunction     = CValue::getOrSession("filterFunction");
$enabled_service    = CValue::getOrSession("active_filter_services", 0);
$only_entree_reelle = CValue::getOrSession("only_entree_reelle", 0);
$type_pec           = CValue::get("type_pec", $sejour->_specs["type_pec"]->_list);
$step               = CView::get("step", "num default|" . CAppUI::gconf("dPadmissions General pagination_step"));
$page               = CView::get("page", "num default|0");

CView::checkin();

$order_way = (CMbString::upper($order_way) === 'DESC') ? 'DESC' : 'ASC';

$type_pref = array();

// Liste des types d'admission possibles
$list_type_admission = $sejour->_specs["_type_admission"]->_list;

if (is_array($services_ids)) {
  CMbArray::removeValue("", $services_ids);
}

if (is_array($sejours_ids)) {
  CMbArray::removeValue("", $sejours_ids);

  // recupere les préférences des differents types de séjours selectionnés par l'utilisateur
  foreach ($sejours_ids as $key) {
    if ($key != 0) {
      $type_pref[] = $list_type_admission[$key];
    }
  }
}

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);
if ($heure) {
  $date_min = CMbDT::dateTime($heure, $date);
  $date_max = CMbDT::dateTime($heure, $date);
}
else {
  $date_min = CMbDT::dateTime("00:00:00", $date);
  $date_max = CMbDT::dateTime("23:59:59", $date);
}

// Entrées de la journée
$group = CGroups::loadCurrent();

$use_perms = CAppUI::gconf("dPadmissions General use_perms");

// Lien avec les patients et les praticiens
$ljoin["patients"] = "sejour.patient_id = patients.patient_id";
$ljoin["users"] = "sejour.praticien_id = users.user_id";

// Filtre sur les services
if (count($services_ids) && $enabled_service) {
  $ljoin["affectation"]        = "affectation.sejour_id = sejour.sejour_id";
  $where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids);
  $where[] = "affectation.entree <= '$date_max' AND affectation.sortie >= '$date_min'";
}
else {
  $where["sejour.entree"]   = "<= '$date_max'";
  $where["sejour.sortie"]   = ">= '$date_min'";
}

// Filtre sur le type du séjour
if (count($type_pref)) {
  $where["sejour.type"] = CSQLDataSource::prepareIn($type_pref);
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ["seances"]));
}

// Filtre sur le praticien
if ($prat_id) {
  $user = CMediusers::get($prat_id);

  if ($user->isAnesth()) {
    $ljoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
    $ljoin['plagesop'] = 'plagesop.plageop_id = operations.plageop_id';
    $where[] = " operations.anesth_id = '$prat_id' OR plagesop.anesth_id = '$prat_id' OR sejour.praticien_id = '$prat_id'";
  }
  else {
    $where['sejour.praticien_id'] = " = '$prat_id'";
  }
}

$where["sejour.group_id"] = "= '$group->_id'";
$where["sejour.annule"]   = "= '0'";

if (!in_array($order_col, array("patient_id", "entree", "sortie", "praticien_id", "_chambre"))) {
  $order_col = "patient_id";
}

$strict_order = true;

if ($order_col == "patient_id") {
  $order = "patients.nom $order_way, patients.prenom $order_way, sejour.entree";
}

if ($order_col == "entree") {
  $order = "sejour.entree $order_way, sejour.sortie $order_way, patients.nom, patients.prenom";
}

if ($order_col == "sortie") {
  $order = "sejour.sortie $order_way, sejour.entree $order_way, patients.nom, patients.prenom";
}

if ($order_col == "praticien_id") {
  $order = "users.user_last_name $order_way, users.user_first_name";
}

if ($order_col == "_chambre") {
  $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";
  $ljoin["lit"]         = "lit.lit_id = affectation.lit_id";
  $ljoin["chambre"]     = "chambre.chambre_id = lit.chambre_id";
  $order = "ISNULL(chambre.rank), chambre.rank $order_way, chambre.nom $order_way, patients.nom, patients.prenom, sejour.entree";
}

if ($only_entree_reelle) {
  $where["sejour.entree_reelle"] = "IS NOT NULL";
}

if ($filterFunction) {
    $ljoin["users_mediboard"] = "users_mediboard.user_id = sejour.praticien_id";
    $where["users_mediboard.function_id"] = $ds->prepare("= ?", $filterFunction);
}

$where["sejour.type_pec"] = CSQLDataSource::prepareIn($type_pec);

$show_curr_affectation = CAppUI::gconf("dPadmissions General show_curr_affectation");

$limit = "$page, $step";

/** @var CSejour[] $sejours */
$sejours = $use_perms ?
  $sejour->loadListWithPerms(PERM_READ, $where, $order, $limit, "sejour.sejour_id", $ljoin, false) :
  $sejour->loadList($where, $order, $limit, "sejour.sejour_id", $ljoin, null, null, false);

$total = $sejour->countList($where, null, $ljoin);

// Récupérations des fonctions pour le filtrage
if ($filterFunction) {
    unset($where["users_mediboard.function_id"]);
}
$ljoin["users_mediboard"] = "users_mediboard.user_id = sejour.praticien_id";
$request = new CRequest();
$request->addSelect('DISTINCT users_mediboard.function_id');
$request->addTable('sejour');
$request->addLJoin($ljoin);
$request->addWhere($where);
$function_ids = $ds->loadColumn($request->makeSelect());
$functions = (new CFunctions())->loadAll($function_ids);

$total_sejours = null;
if ($heure) {
  $date_min = CMbDT::dateTime("00:00:00", $date);
  $date_max = CMbDT::dateTime("23:59:59", $date);
  $where["sejour.entree"]   = "<= '$date_max'";
  $where["sejour.sortie"]   = ">= '$date_min'";
  $total_sejours = $sejour->countList($where, null, $ljoin);
}

$datetime = ($heure) ? new DateTime($date.' '.$heure) : new DateTime();

$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");
$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");

// Chargement optimisée des prestations
CSejour::massCountPrestationSouhaitees($sejours);

CStoredObject::massLoadBackRefs($sejours, "notes");
CStoredObject::massLoadBackRefs($patients, "dossier_medical");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
CStoredObject::massLoadBackRefs($patients, "patient_handicaps");

// Chargement des NDA
CSejour::massLoadNDA($sejours);
// Chargement des IPP
CPatient::massLoadIPP($patients);

// Chargement optimisé des prestations
CSejour::massCountPrestationSouhaitees($sejours);

$operations = CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", array("annulee" => "= '0'"));

CStoredObject::massLoadBackRefs($sejours, "billing_periods");
CStoredObject::massLoadBackRefs($operations, "actes_ngap", "lettre_cle DESC");

$order = "code_association, code_acte,code_activite, code_phase, acte_id";
CStoredObject::massLoadBackRefs($operations, "actes_ccam", $order);

$affectations = $show_curr_affectation ?
    CSejour::massLoadCurrAffectation($sejours, $datetime->format('Y-m-d H:i:s'))
    : CStoredObject::massLoadBackRefs($sejours, "affectations", 'sortie DESC');
CAffectation::massUpdateView($affectations, $datetime->format('Y-m-d H:i:s'));

CStoredObject::massLoadFwdRef($affectations, 'service_id');
$lits = CStoredObject::massLoadFwdRef($affectations, 'lit_id');
$chambres = CStoredObject::massLoadFwdRef($lits, 'chambre_id');
CStoredObject::massLoadFwdRef($chambres, 'service_id');

if (CModule::getActive("maternite")) {
  $parent_affectations = CStoredObject::massLoadFwdRef($affectations, "parent_affectation_id");
  CStoredObject::massLoadFwdRef($parent_affectations, "sejour_id");
}

foreach ($sejours as $sejour_id => $_sejour) {
  $praticien = $_sejour->loadRefPraticien();

  $_sejour->checkDaysRelative($date);

  // Chargement du patient
  $patient = $_sejour->loadRefPatient();

  $patient->updateBMRBHReStatus($_sejour);
  $patient->loadRefsPatientHandicaps();

  $dossier_medical = $patient->loadRefDossierMedical(false);

  // Chargement des notes du séjour
  $_sejour->loadRefsNotes();

  // Chargement des interventions
  $whereOperations = array("annulee" => "= '0'");
  $_sejour->loadRefsOperations($whereOperations);
  foreach ($_sejour->_ref_operations as $operation) {
    $operation->loadRefsActes();
  }

  // Chargement de l'affectation
  if ($show_curr_affectation) {
    $affectation = $_sejour->_ref_curr_affectation;
  }
  else {
    $_sejour->loadRefsAffectations();
    $affectation = $_sejour->_ref_first_affectation;
  }

  $affectation = $_sejour->loadRefFirstAffectation();
  if (CModule::getActive("maternite")) {
    $affectation->loadRefParentAffectation()->loadRefSejour();
  }
}

if (CAppUI::gconf("dPadmissions General show_deficience")) {
  $dossiers = CMbArray::pluck($sejours, "_ref_patient", "_ref_dossier_medical");
  CDossierMedical::massCountAntecedentsByType($dossiers, "deficience");
}

// Si la fonction selectionnée n'est pas dans la liste des fonction, on la rajoute
if ($filterFunction && !array_key_exists($filterFunction, $functions)) {
  $_function = new CFunctions();
  $_function->load($filterFunction);
  $functions[$filterFunction] = $_function;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("hier"            , $hier);
$smarty->assign("demain"          , $demain);
$smarty->assign("date_min"        , $date_min);
$smarty->assign("date_max"        , $date_max);
$smarty->assign("date_demain"     , $date_demain);
$smarty->assign("date_actuelle"   , $date_actuelle);
$smarty->assign("date"            , $date);
$smarty->assign("order_col"       , $order_col);
$smarty->assign("order_way"       , $order_way);
$smarty->assign("sejours"         , $sejours);
$smarty->assign("total_sejours"   , $total_sejours);
$smarty->assign("prestations"     , CPrestation::loadCurrentList());
$smarty->assign("canAdmissions"   , CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients"     , CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp"   , CModule::getCanDo("dPplanningOp"));
$smarty->assign("functions"       , $functions);
$smarty->assign("filterFunction"  , $filterFunction);
$smarty->assign("which"           , $show_curr_affectation ? "curr" : "first");
$smarty->assign("heure"           , $heure);
$smarty->assign('enabled_service' , $enabled_service);
$smarty->assign('total'           , $total);
$smarty->assign('step'            , $step);
$smarty->assign('page'            , $page);


$smarty->display("inc_vw_presents.tpl");
