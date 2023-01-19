<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id         = CValue::get("sejour_id");
$operation_id      = CValue::get("operation_id");
$operateur_ids     = CValue::get("operateur_ids");

if (!is_array($operateur_ids)) {
  $operateur_ids = explode("-", $operateur_ids);
  CMbArray::removeValue("", $operateur_ids);
}

if (count($operateur_ids)) {
  $operateur = new CMediusers;
  $where = array(
    "user_id" => "IN(" . implode(",", $operateur_ids) . ")",
  );
  $operateurs = $operateur->loadList($where);
}
else {
  $operateurs = array();
}

$poses = array();
if ($operation_id) {
  $interv = new COperation;
  $interv->load($operation_id);

  CAccessMedicalData::logAccess($interv);

  $poses = $interv->loadRefsPosesDispVasc(true);
}
elseif ($sejour_id) {
  $sejour = new CSejour;
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $poses = $sejour->loadRefsPosesDispVasc(true);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("poses", $poses);
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("operation_id", $operation_id);
$smarty->assign("operateur_ids", $operateur_ids);

$smarty->display("inc_list_pose_disp_vasc");
