<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\Bloc\PreparationSalle;

CCanDo::checkRead();

CView::checkin();
CView::enforceSlave();

$curr_group = CGroups::loadCurrent();

$ds = $curr_group->getDS();

$date_min = CMbDT::date();
$date_max = CMbDT::date("+1 day");

$where = [
  "sejour.group_id" => $ds->prepare("= ?", $curr_group->_id),
  "operations.date" => $ds->prepare("BETWEEN ?1 AND ?2", $date_min, $date_max)
];

$ljoin = [
  "operations" => "operations.operation_id  = materiel_operatoire.operation_id",
  "sejour"     => "sejour.sejour_id = operations.sejour_id"
];

$operations_ids = (new CMaterielOperatoire())->loadColumn("operations.operation_id", $where, $ljoin);

$operations = (new COperation())->loadList(["operation_id" => CSQLDataSource::prepareIn($operations_ids)]) ?? [];

CStoredObject::massLoadBackRefs($operations, "materiels_operatoires");
$sejours = CStoredObject::massLoadFwdRef($operations, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadFwdRef($operations, "plageop_id");
CStoredObject::massLoadFwdRef($operations, "salle_id");

foreach ($operations as $_operation) {
  $_operation->loadRefPatient();
  $_operation->loadRefPlageOp();
  $_operation->updateSalle();
  $_operation->loadRefsMaterielsOperatoires(true, false, true);
}

$planning = null;
try {
  $planning = new PreparationSalle($operations);
  $planning->sortByTime();
}
catch (CMbModelNotFoundException $e) {
  CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
}

$smarty = new CSmartyDP();

$smarty->assign("planning", $planning);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);

$smarty->display("offline_preparation_salle");