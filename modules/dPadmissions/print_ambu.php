<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

// Récupération des dates
$date       = CView::get("date", "date default|now", true);
$type       = CView::get("type", "str default|ambu", true);
$service_id = CView::get("service_id", "ref class|CService", true);

CView::checkin();

// Initialisation
$sejour = new CSejour();
$sejours = array();

// Récupération de la liste des services
$where = array();
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";
$service = new CService();
$services = $service->loadGroupList($where);

// Récupération des sorties du jour
$limit1 = $date." 00:00:00";
$limit2 = $date." 23:59:59";

// ljoin pour filtrer par le service
$ljoin["affectation"] = "sejour.sejour_id = affectation.sejour_id";
$ljoin["service"]     = "affectation.service_id = service.service_id";

if ($service_id) {
  $where["service.service_id"] = " = '$service_id'";
}

$group = CGroups::loadCurrent();

$order = "service.nom, sejour.entree_reelle";
$where["sortie_prevue"]   = "BETWEEN '$limit1' AND '$limit2'";
$where["type"]            = " = '$type'";
$where["sejour.annule"]   = " = '0'";
$where["sejour.group_id"] = " = '$group->_id'";
/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, $order, null, null, $ljoin);

CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($sejours, "praticien_id");

$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie ASC");
CAffectation::massUpdateView($affectations);
CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");

foreach ($sejours as $key => $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefPraticien();
  $_sejour->loadRefsAffectations("sortie ASC");
  $_sejour->loadRefsOperations();
  $_sejour->_duree = CMbDT::subTime(CMbDT::time($_sejour->entree_reelle), CMbDT::time($_sejour->sortie_reelle));

  $_sejour->_ref_last_operation->loadRefSortieLocker()->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("service_id", $service_id);
$smarty->assign("sejours"   , $sejours);
$smarty->assign("services"  , $services);
$smarty->assign("date"      , $date);
$smarty->assign("type"      , $type);

$smarty->display("print_ambu.tpl");
