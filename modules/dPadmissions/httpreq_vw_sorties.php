<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour = new CSejour();
$ds     = $sejour->getDS();

$type             = CView::get("type", "str");
$services_ids     = CView::get("services_ids", "str", true);
$sejours_ids      = CView::get("sejours_ids", "str", true);
$prat_id          = CView::get("prat_id", "ref class|CMediusers", true);
$only_confirmed   = CView::get("only_confirmed", "str", true);
$selSortis        = CView::get("selSortis", "str default|0", true);
$order_col        = CView::get("order_col", "str default|patient_id", true);
$order_way        = CView::get("order_way", "str default|ASC", true);
$date             = CView::get("date", "date default|now", true);
$next             = CMbDT::date("+1 DAY", $date);
$filterFunction   = CView::get("filterFunction", "ref class|CFunctions", true);
$period           = CView::get("period", "str", true);
$enabled_service  = CView::get("active_filter_services", "bool default|0", true);
$type_pec         = CView::get("type_pec", array('str' , 'default' => $sejour->_specs["type_pec"]->_list));
$mode_sortie      = CView::get("mode_sortie", array('str' , 'default' => array("all")));
$print_global     = CView::get("print_global", "bool default|0");
$reglement_dh     = CView::get('reglement_dh', 'enum list|all|payed|not_payed');
$circuits_ambu    = CView::get("circuit_ambu", array('str', 'default' => $sejour->_specs["circuit_ambu"]->_list), true);
$prestations_p_ids = CView::get("prestations_p_ids", array("str", "default" => array()), true);
$step             = CView::get("step", "num default|" . CAppUI::gconf("dPadmissions General pagination_step"));
$page             = CView::get("page", "num default|0");
CView::checkin();

if (is_array($prestations_p_ids)) {
  CMbArray::removeValue("", $prestations_p_ids);
} else {
  $prestations_p_ids = [$prestations_p_ids];
}

// Pour plus de simplicité, on vide le tableau des filtres de presta si le filtre est "Toutes les prestas"
if (is_array($prestations_p_ids)) {
  if (in_array("all", $prestations_p_ids)) {
    $prestations_p_ids = null;
  }
}

$type_pref = array();

// Liste des types d'admission possibles
$list_type_admission = $sejour->_specs["_type_admission"]->_list;

if (is_array($services_ids)) {
  CMbArray::removeValue("", $services_ids);
}

if (is_array($circuits_ambu)) {
  CMbArray::removeValue("", $circuits_ambu);
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

foreach ($type_pref as $_type) {
  if ($_type == "ambu") {
    $type = "ambu";
  }
  elseif ($_type == "exte") {
    $type = "exte";
  }
}

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date);
//$date_min = "2012-09-23 00:00:00";
//$date_max = "2012-09-25 23:59:59";

if ($period) {
  $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");
  if ($period == "matin") {
    // Matin
    $date_max = CMbDT::dateTime($hour, $date);
  }
  else {
    // Soir
    $date_min = CMbDT::dateTime($hour, $date);
    $date_min = CMbDT::dateTime("+1 SEC", $date_min);
  }
}

// Sorties de la journée
$group = CGroups::loadCurrent();

$use_perms = CAppUI::gconf("dPadmissions General use_perms");

// Lien avec les patients et les praticiens
$ljoin["patients"]    = "sejour.patient_id = patients.patient_id";
$ljoin["users"]       = "sejour.praticien_id = users.user_id";

// Filtre sur les services
if ($enabled_service && count($services_ids)) {
  $ljoin["affectation"]        = "affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie";
  $where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids);
}

// Filtre sur le type du séjour
if (count($type_pref)) {
  $where["sejour.type"] = CSQLDataSource::prepareIn($type_pref);
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ["seances"]));
}

// filtre sur le type de circuit en ambulatoire
if (CAppUI::gconf("dPplanningOp CSejour show_circuit_ambu") && $circuits_ambu && count($circuits_ambu)) {
  $where[] = "sejour.circuit_ambu " . CSQLDataSource::prepareIn($circuits_ambu) . " OR sejour.circuit_ambu IS NULL";
}

if (!(in_array('', $type_pec) && count($type_pec) == 1)) {
  $where[] = "sejour.type_pec " . CSQLDataSource::prepareIn($type_pec) . ' OR sejour.type_pec IS NULL';
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

// Seulement les confirmées par le praticien
if ($only_confirmed != "") {
  $where["sejour.confirme"] = $only_confirmed ? "IS NOT NULL" : "IS NULL";
}

$where["sejour.group_id"] = "= '$group->_id'";
$where["sejour.sortie"]   = "BETWEEN '$date_min' AND '$date_max'";
$where["sejour.annule"]   = "= '0'";

$where_mode_sortie        = "sejour.mode_sortie ".CSQLDataSource::prepareIn($mode_sortie);
foreach ($mode_sortie as $_key => $_sortie) {
  if ($_sortie === "") {
    $where_mode_sortie = "($where_mode_sortie OR sejour.mode_sortie IS NULL)";
    break;
  }
  if ($_sortie === "all") {
    $where_mode_sortie = false;
    break;
  }
}
if ($where_mode_sortie) {
  $where[] = $where_mode_sortie;
}

switch ($selSortis) {
  case 'np':
    $where['sortie_preparee'] = "= '0'";
    break;

  case 'n':
    $where[] = "(sortie_reelle IS NULL)";
    break;

  default:
}

if ($reglement_dh && $reglement_dh != 'all') {
  if (!isset($ljoin["operations"])) {
    $ljoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
  }
  if ($reglement_dh == 'payed') {
    $where[] = "((operations.depassement > 0 AND operations.reglement_dh_chir != 'non_regle') 
        OR operations.depassement = 0 OR operations.depassement IS NULL)
      AND ((operations.depassement_anesth > 0 AND operations.reglement_dh_anesth != 'non_regle') 
        OR operations.depassement_anesth = 0 OR operations.depassement_anesth IS NULL)
      AND (operations.depassement > 0 OR operations.depassement_anesth > 0)";
  }
  else {
    $where[] = "(operations.depassement > 0 AND operations.reglement_dh_chir = 'non_regle')
      OR (operations.depassement_anesth > 0 AND operations.reglement_dh_anesth = 'non_regle')";
  }
}

if (!in_array($order_col, array("patient_id", "sortie_prevue", "praticien_id"))) {
  $order_col = "patient_id";
}

if ($order_col == "patient_id") {
  $order = "patients.nom $order_way, patients.prenom $order_way, sejour.sortie_prevue";
}

if ($order_col == "sortie_prevue") {
  $order = "sejour.sortie_prevue $order_way, patients.nom, patients.prenom";
}

if ($order_col == "praticien_id") {
  $order = "users.user_last_name $order_way, users.user_first_name $order_way";
}

if ($filterFunction) {
    $ljoin["users_mediboard"] = "users_mediboard.user_id = sejour.praticien_id";
    $where["users_mediboard.function_id"] = $ds->prepare("= ?", $filterFunction);
}

if ($prestations_p_ids) {
    $ljoin["item_liaison"] = "item_liaison.sejour_id = sejour.sejour_id";
    $ljoin["item_prestation"] = "item_prestation.item_prestation_id = item_liaison.item_souhait_id";
    $ljoin["prestation_ponctuelle"] = "prestation_ponctuelle.prestation_ponctuelle_id = item_prestation.object_id AND item_prestation.object_class = 'CPrestationPonctuelle'";
    $where["prestation_ponctuelle.prestation_ponctuelle_id"] = $ds->prepareIn($prestations_p_ids);
}

$limit = $print_global ? null : "$page, $step";

/** @var CSejour[] $sejours */
$sejours = $use_perms ?
  $sejour->loadListWithPerms(PERM_READ, $where, $order, $limit, 'sejour.sejour_id', $ljoin) :
  $sejour->loadList($where, $order, $limit, 'sejour.sejour_id', $ljoin);

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

$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($sejours, "etablissement_sortie_id");
CStoredObject::massLoadFwdRef($sejours, "service_sortie_id");
$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");
$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", 'sortie DESC');
CAffectation::massUpdateView($affectations);

// Chargement optimisée des prestations
CSejour::massLoadPrestationRealisees($sejours);

CStoredObject::massLoadBackRefs($sejours, "notes");
CStoredObject::massLoadBackRefs($patients, "dossier_medical");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

$operations = CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", array("annulee" => "= '0'"));
CStoredObject::massLoadFwdRef($operations, 'plageop_id');
CStoredObject::massLoadBackRefs($operations, "actes_ngap", "lettre_cle DESC");

$order = "code_association, code_acte,code_activite, code_phase, acte_id";
CStoredObject::massLoadBackRefs($operations, "actes_ccam", $order);

if (CModule::getActive("maternite")) {
  $parent_affectations = CStoredObject::massLoadFwdRef($affectations, "parent_affectation_id");
  $parent_sejours      = CStoredObject::massLoadFwdRef($parent_affectations, "sejour_id");
}

// Chargement des NDA
CSejour::massLoadNDA($sejours);
// Chargement des IPP
CPatient::massLoadIPP($patients);

$maternite_active = CModule::getActive("maternite");

if (CModule::getActive("appFineClient")) {
  CAppFineClient::massloadIdex($sejours, $group->_id);
  CAppFineClient::massloadIdex($patients, $group->_id);
}

foreach ($sejours as $sejour_id => $_sejour) {
  // Filtre sur la fonction du praticien
  $praticien = $_sejour->loadRefPraticien(1);

  // Chargement du patient
  $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);

  if (CModule::getActive("appFineClient")) {
    $_sejour->_ref_patient->loadRefStatusPatientUser();
  }

  // Chargements des notes sur le séjour
  $_sejour->loadRefsNotes();

  // Chargement des interventions
  $whereOperations = array("annulee" => "= '0'");
  $_sejour->loadRefsOperations($whereOperations);

  foreach ($_sejour->_ref_operations as $operation) {
    $operation->loadRefsActes();
    $operation->loadRefPlageOp();
  }

  // Chargement des affectation
  $_sejour->loadRefsAffectations();

  if ($maternite_active) {
    $_sejour->loadRefFirstAffectation()->loadRefParentAffectation()->loadRefSejour();
    $_sejour->_sejours_enfants_ids = CMbArray::pluck($_sejour->loadRefsNaissances(), "sejour_enfant_id");
  }

  // Chargement des modes de sortie
  $_sejour->loadRefEtablissementTransfert();
  $_sejour->loadRefServiceMutation();
  // Chargement des appels
  $_sejour->loadRefsAppel('sortie');

}

// Si la fonction selectionnée n'est pas dans la liste des fonction, on la rajoute
$flag_contextual_icons = false;
$systeme_prestations_tiers = CAppUI::gconf("dPhospi prestations systeme_prestations_tiers");

if (
  CModule::getActive("web100T")
  || CModule::getActive("softway")
  || CModule::getActive("novxtelHospitality")
  || CModule::getActive("notifications")
  || CAppUI::gconf("dPadmissions General show_deficience")
  || !($systeme_prestations_tiers == 'Aucun' || !CModule::getActive($systeme_prestations_tiers))
  || !($systeme_prestations_tiers == 'softway' && CAppUI::gconf("softway presta send_presta_immediately"))
) {
  $flag_contextual_icons = true;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("flag_contextual_icons", $flag_contextual_icons);
$smarty->assign("hier"            , $hier);
$smarty->assign("demain"          , $demain);
$smarty->assign("date_min"        , $date_min);
$smarty->assign("date_max"        , $date_max);
$smarty->assign("date_demain"     , $date_demain);
$smarty->assign("date_actuelle"   , $date_actuelle);
$smarty->assign("date"            , $date);
$smarty->assign("type"            , $type);
$smarty->assign("selSortis"       , $selSortis);
$smarty->assign("order_col"       , $order_col);
$smarty->assign("order_way"       , $order_way);
$smarty->assign("sejours"         , $sejours);
$smarty->assign("prestations"     , CPrestation::loadCurrentList());
$smarty->assign("canAdmissions"   , CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients"     , CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp"   , CModule::getCanDo("dPplanningOp"));
$smarty->assign("functions"       , $functions);
$smarty->assign("filterFunction"  , $filterFunction);
$smarty->assign("period"          , $period);
$smarty->assign('enabled_service' , $enabled_service);
$smarty->assign('print_global'    , $print_global);
$smarty->assign('total'           , $total);
$smarty->assign('step'            , $step);
$smarty->assign('page'            , $page);

$smarty->display("inc_vw_sorties.tpl");
