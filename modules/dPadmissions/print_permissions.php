<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
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

$type_externe   = CView::get("type_externe", "str default|depart", true);
$date           = CView::get("date", "date default|now", true);
$filterFunction = CView::get("filterFunction", "ref class|CFunctions", true);

CView::checkin();

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
$ljoin["users"]    = "sejour.praticien_id = users.user_id";
$ljoin["service"]  = "affectation.service_id = service.service_id";

$where["service.externe"] = "= '1'";
$where[] = "`sejour`.`type` " . CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence()) . " AND `sejour`.`type` != 'seances'";
$where["sejour.group_id"] = "= '$group->_id'";

if ($type_externe == "depart") {
  $where["affectation.entree"] = "BETWEEN '$date_min' AND '$date_max'";
}
else {
  $where["affectation.sortie"] = "BETWEEN '$date_min' AND '$date_max'";
}
$where["sejour.annule"]   = "= '0'";

$affectation = new CAffectation();
$order = "entree, sortie";

/** @var CAffectation[] $affectations */
$affectations = $use_perms ?
  $affectation->loadListWithPerms(PERM_READ, $where, $order, null, null, $ljoin) :
  $affectation->loadList($where, $order, null, null, $ljoin);

CAffectation::massUpdateView($affectation);
$sejours      = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
$patients     = CStoredObject::massLoadFwdRef($sejours     , "patient_id");
$praticiens   = CStoredObject::massLoadFwdRef($sejours     , "praticien_id");
$functions    = CStoredObject::massLoadFwdRef($praticiens  , "function_id");
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

  if ($filterFunction && $filterFunction != $praticien->function_id) {
    unset($sejours[$sejour->_id]);
    continue;
  }

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

$smarty->assign("date"          , $date);
$smarty->assign("type_externe"  , $type_externe);
$smarty->assign("affectations"  , $affectations);
$smarty->assign("functions"     , $functions);
$smarty->assign("filterFunction", $filterFunction);

$smarty->display("print_permissions.tpl");
