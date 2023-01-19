<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$group = CGroups::loadCurrent();

$services_ids = CView::get("suivi_services_ids", "str", true);
if ($services_ids && $services_ids[0] === "") {
  $services_ids = CView::setSession("suivi_services_ids", null);
}
$blocs_ids = CView::get("suivi_blocs_ids", "str", true);
if ($blocs_ids && $blocs_ids[0] === "") {
  $blocs_ids = CView::setSession("suivi_blocs_ids", null);
}
$date_suivi = CView::get("date_suivi", "date default|now", true);
CView::checkin();

$listOps = array();

// Liste des services
$service            = new CService();
$where              = array();
$where["group_id"]  = "= '$group->_id'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $service->loadListWithPerms(PERM_READ, $where, $order);

// Liste des services selectionnés
$listServices = array();
foreach ($services as $_service) {
  $listServices[$_service->_id] = array();
}
$listServices["NP"] = array();

// Liste des blocs
$bloc              = new CBlocOperatoire();
$where             = array();
$where["group_id"] = "= '$group->_id'";
$where["actif"]    = "= '1'";
$order             = "nom";
$blocs             = $bloc->loadListWithPerms(PERM_READ, $where, $order);

CStoredObject::massLoadBackRefs($blocs, "salles");

// Listes des interventions
$operation = new COperation();
$ljoin     = array(
  "plagesop"   => "`operations`.`plageop_id` = `plagesop`.`plageop_id`",
  "sallesbloc" => "`operations`.`salle_id` = `sallesbloc`.`salle_id`",
  "sejour"     => "`operations`.`sejour_id` = `sejour`.`sejour_id`");
$where     = array();
$where[]   = "`plagesop`.`date` = '$date_suivi' OR `operations`.`date` = '$date_suivi'";
if ($blocs_ids) {
  $where["sallesbloc.bloc_id"] = CSQLDataSource::prepareIn($blocs_ids);
}
$where["operations.annulee"] = "= '0'";
$where["sejour.group_id"]    = "= '$group->_id'";
$order                       = "operations.time_operation";
/** @var COperation[] $listOps */
$listOps = $operation->loadList($where, $order, null, null, $ljoin);

$chirs = CStoredObject::massLoadFwdRef($listOps, "chir_id");
CStoredObject::massLoadFwdRef($chirs, "function_id");
CStoredObject::massLoadFwdRef($listOps, "plageop_id");
$sejours = CStoredObject::massLoadFwdRef($listOps, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");

// Chargement des infos des interventions
foreach ($listOps as $_key => $_op) {
  $_op->loadRefChir();
  $_op->_ref_chir->loadRefFunction();
  $_op->loadRefSejour();
  $_op->_ref_sejour->loadRefPatient();
  $_op->loadRefAffectation(false)->loadRefLit()->loadRefChambre();
  $_op->loadExtCodesCCAM();

  if ($services_ids
    && $_op->_ref_affectation->service_id
    && !in_array($_op->_ref_affectation->service_id, $services_ids)
  ) {
    unset($listOps[$_key]);
    continue;
  }
  if ($_op->_ref_affectation->_id) {
    $listServices[$_op->_ref_affectation->service_id][$_op->_id] = $_op;
  }
  else {
    $listServices["NP"][$_op->_id] = $_op;
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date_suivi", $date_suivi);
$smarty->assign("listServices", $listServices);
$smarty->assign("blocs", $blocs);
$smarty->assign("services", $services);
$smarty->assign("blocs_ids", $blocs_ids);
$smarty->assign("services_ids", $services_ids);

$smarty->display("vw_suivi_bloc.tpl");
