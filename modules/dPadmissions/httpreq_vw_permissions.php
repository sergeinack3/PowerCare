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
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

// Type d'admission
$type           = CView::get("type", "str", true);
$type_externe   = CView::get("type_externe", "str default|depart", true);
$date           = CView::get("date", "date default|now", true);
$next           = CMbDT::date("+1 DAY", $date);
$filterFunction = CView::get("filterFunction", "ref class|CFunctions", true);
$step           = CView::get("step", "num default|" . CAppUI::gconf("dPadmissions General pagination_step"));
$page           = CView::get("page", "num default|0");


CView::checkin();

$affectation = new CAffectation();
$ds          = $affectation->getDS();

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");

$hier   = CMbDT::date("- 1 day", $date);
$demain = CMbDT::date("+ 1 day", $date);

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date);

// Chargement des prestations
$prestations = CPrestation::loadCurrentList();

// Entrées de la journée
$group = CGroups::loadCurrent();

$use_perms = CAppUI::gconf("dPadmissions General use_perms");

// Liens diverses
$ljoin["sejour"]   = "affectation.sejour_id = sejour.sejour_id";
$ljoin["patients"] = "sejour.patient_id = patients.patient_id";
$ljoin["service"]  = "affectation.service_id = service.service_id";

$where["service.externe"] = "= '1'";

// Filtre sur la fonction
if ($filterFunction) {
    $ljoin["users_mediboard"] = "users_mediboard.user_id = sejour.praticien_id";
    $where["users_mediboard.function_id"] = $ds->prepare("= ?", $filterFunction);
}

// Filtre sur le type du séjour
if ($type == "ambucomp") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp'";
}
elseif ($type == "ambucompssr") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr'";
}
elseif ($type) {
  $where["sejour.type"] = $ds->prepare("= ?" ,$type);
}
else {
  $where[] = "`sejour`.`type` " . CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence()) . " AND `sejour`.`type` != 'seances'";
}

$where["sejour.group_id"] = $ds->prepare("= ?", $group->_id);
if ($type_externe == "depart") {
  $where["affectation.entree"] = $ds->prepareBetween($date_min, $date_max);
}
else {
  $where["affectation.sortie"] = $ds->prepareBetween($date_min, $date_max);
}
$where["sejour.annule"]   = "= '0'";

$order = "entree, sortie";

$limit = "$page, $step";

/** @var CAffectation[] $affectations */
$affectations = $use_perms ?
  $affectation->loadListWithPerms(PERM_READ, $where, $order, $limit, null, $ljoin) :
  $affectation->loadList($where, $order, $limit, null, $ljoin);

$total = $affectation->countList($where, null, $ljoin);

// Récupérations des fonctions pour le filtrage
if ($filterFunction) {
    unset($where["users_mediboard.function_id"]);
}
$ljoin["users_mediboard"] = "users_mediboard.user_id = sejour.praticien_id";
$request = new CRequest();
$request->addSelect('DISTINCT users_mediboard.function_id');
$request->addTable('affectation');
$request->addLJoin($ljoin);
$request->addWhere($where);
$function_ids = $ds->loadColumn($request->makeSelect());
$functions = (new CFunctions())->loadAll($function_ids);

CAffectation::massUpdateView($affectations);
$sejours      = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
$patients     = CStoredObject::massLoadFwdRef($sejours     , "patient_id");
$praticiens   = CStoredObject::massLoadFwdRef($sejours     , "praticien_id");
CStoredObject::massLoadFwdRef($praticiens  , "function_id");
$lits         = CStoredObject::massLoadFwdRef($affectations, "lit_id");
$chambres     = CStoredObject::massLoadFwdRef($lits        , "chambre_id");
$services     = CStoredObject::massLoadFwdRef($chambres    , "service_id");

CStoredObject::massLoadBackRefs($sejours, "notes");

// Chargement des NDA
CSejour::massLoadNDA($sejours);

// Chargement des IPP
CPatient::massLoadIPP($patients);

foreach ($affectations as $affectation_id => $affectation) {
  $affectation->loadView();
  $affectation->loadRefsAffectations();
  $affectation->_ref_prev->loadView();
  $affectation->_ref_prev->updateView();
  $affectation->_ref_next->loadView();
  $affectation->_ref_next->updateView();
  $sejour    = $affectation->loadRefSejour();
  $praticien = $sejour->loadRefPraticien();

  // Chargement du patient
  $sejour->loadRefPatient();

  // Chargement des notes sur le séjour
  $sejour->loadRefsNotes();
}

// Si la fonction selectionnée n'est pas dans la liste des fonction, on la rajoute
if ($filterFunction && !array_key_exists($filterFunction, $functions)) {
  $_function = new CFunctions();
  $_function->load($filterFunction);
  $functions[$filterFunction] = $_function;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("hier"          , $hier);
$smarty->assign("demain"        , $demain);
$smarty->assign("date_min"      , $date_min);
$smarty->assign("date_max"      , $date_max);
$smarty->assign("date_demain"   , $date_demain);
$smarty->assign("date_actuelle" , $date_actuelle);
$smarty->assign("date"          , $date);
$smarty->assign("type_externe"  , $type_externe);
$smarty->assign("affectations"  , $affectations);
$smarty->assign("canAdmissions" , CModule::getCanDo("dPadmissions"));
$smarty->assign("canPatients"   , CModule::getCanDo("dPpatients"));
$smarty->assign("canPlanningOp" , CModule::getCanDo("dPplanningOp"));
$smarty->assign("functions"     , $functions);
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign('total'         , $total);
$smarty->assign('step'          , $step);
$smarty->assign('page'          , $page);

$smarty->display("inc_vw_permissions.tpl");
