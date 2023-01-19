<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CPrestation;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

global $m;

// On sauvegarde le module pour que les mises en session des paramètes se fassent
// dans le module depuis lequel on accède à la ressource
$save_m = $m;

// Type d'admission
$current_m      = CView::get("current_m", "str");

$m = $current_m;

$service_id     = CView::get("service_id", "ref class|CService", true);
$prat_id        = CView::get("prat_id", "ref class|CMediusers", true);
$recuse         = CView::get("recuse", "str default|-1", true);
$envoi_mail     = CView::get("envoi_mail", "bool default|0", true);
$order_col      = CView::get("order_col", "str default|patient_id", true);
$order_way      = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$date           = CView::get("date", "date default|now", true);
$next           = CMbDT::date("+1 DAY", $date);
$filterFunction = CView::get("filterFunction", "bool", true);

CView::checkin();

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:00", $date);

// Chargement des prestations
$prestations = CPrestation::loadCurrentList();

// Entrées de la journée
$sejour = new CSejour();

$group = CGroups::loadCurrent();

// Lien avec les patients et les praticiens
$ljoin["patients"] = "sejour.patient_id = patients.patient_id";
$ljoin["users"]    = "sejour.praticien_id = users.user_id";

// Filtre sur les services
if ($service_id) {
  $ljoin["affectation"]        = "affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie_prevue";
  $where["affectation.service_id"] = "= '$service_id'";
}

// Filtre sur le type du séjour
if ($current_m == "ssr" || $current_m == "psy") {
  $where["type"] = "= '$current_m'";
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
$where["sejour.entree"]   = "BETWEEN '$date_min' AND '$date_max'";

if ($envoi_mail == 1) {
  $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
  $where["operations.envoi_mail"] = "IS NOT NULL";
}
else {
  $where["sejour.recuse"]   = "= '$recuse'";
  if ($recuse != 1) {
    $where["sejour.annule"]   = "= '0'";
  }
}
if ($order_col != "patient_id" && $order_col != "entree_prevue" && $order_col != "praticien_id") {
  $order_col = "patient_id";
}

if ($order_col == "patient_id") {
  $order = "patients.nom $order_way, patients.prenom $order_way, sejour.entree_prevue";
}

if ($order_col == "entree_prevue") {
  $order = "sejour.entree_prevue $order_way, patients.nom, patients.prenom";
}

if ($order_col == "praticien_id") {
  $order = "users.user_last_name $order_way, users.user_first_name";
}

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, $order, null, null, $ljoin);

$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
$functions  = CStoredObject::massLoadFwdRef($praticiens, "function_id");

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

// Pour l'envoi de mail, on instancie une nouvelle opération
$operation = new COperation();

foreach ($sejours as $sejour_id => $_sejour) {
  $praticien = $_sejour->loadRefPraticien();
  $_sejour->loadRefFicheAutonomie();
  
  if ($filterFunction && $filterFunction != $praticien->function_id) {
    unset($sejours[$sejour_id]);
    continue;
  }
  
  // Chargement du patient
  $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
  
  // Chargment du numéro de dossier
  $whereOperations = array("annulee" => "= '0'");

  // Chargement de l'affectation
  $_sejour->loadRefsAffectations();
  $_sejour->_ref_first_affectation->updateView();

  // Pour l'envoi de mail, afficher une enveloppe pour les interventions modifiées par le chirurgien
  if ($envoi_mail) {
    $where = array(
      "sejour.sejour_id" => "= '$_sejour->_id'",
      "user_log.user_id" => "= operations.chir_id"
    );
    $ljoin = array(
     "sejour" => "sejour.sejour_id = operations.sejour_id",
     "user_log" => "user_log.object_id = operations.operation_id AND user_log.object_class = 'COperation'"
    );
    // @todo déclaration de la variable à réaliser
    $_sejour->_envoi_mail = $operation->countList($where, null, $ljoin);
  }
}

// Si la fonction selectionnée n'est pas dans la liste des fonction, on la rajoute
if ($filterFunction && !array_key_exists($filterFunction, $functions)) {
  $_function = new CFunctions();
  $_function->load($filterFunction);
  $functions[$filterFunction] = $_function;
}


$m = $save_m;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("current_m"     , $current_m);
$smarty->assign("hier"          , $hier);
$smarty->assign("demain"        , $demain);
$smarty->assign("date_min"      , $date_min);
$smarty->assign("date_max"      , $date_max);
$smarty->assign("date_demain"   , $date_demain);
$smarty->assign("date_actuelle" , $date_actuelle);
$smarty->assign("date"          , $date);
$smarty->assign("recuse"        , $recuse);
$smarty->assign("order_col"     , $order_col);
$smarty->assign("order_way"     , $order_way);
$smarty->assign("sejours"       , $sejours);
$smarty->assign("prestations"   , $prestations);
$smarty->assign("canAdmissions" , CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients"   , CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp" , CModule::getCanDo("dPplanningOp"));
$smarty->assign("functions"     , $functions);
$smarty->assign("filterFunction", $filterFunction);

$smarty->display("inc_vw_sejours.tpl");
