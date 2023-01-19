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
use Ox\Mediboard\PlanningOp\CPoseDispositifVasculaire;

CCanDo::checkEdit();

// TODO check droit sur dPsalleOp plutot

$pose_disp_vasc_id = CValue::get("pose_disp_vasc_id");
$sejour_id         = CValue::get("sejour_id");
$operation_id      = CValue::get("operation_id");
$operateur_ids     = CValue::get("operateur_ids");

CAccessMedicalData::logAccess("CSejour-$sejour_id");

if (!is_array($operateur_ids)) {
  $operateur_ids = explode("-", $operateur_ids);
  CMbArray::removeValue("", $operateur_ids);
}

$operateur = new CMediusers;

if (count($operateur_ids)) {
  $where = array(
    "user_id" => "IN(".implode(",", $operateur_ids).")",
  );
  $operateurs = $operateur->loadList($where);
}
else {
  $operateurs = array();
}

$pose = new CPoseDispositifVasculaire;
$pose->load($pose_disp_vasc_id);

if (!$pose->_id) {
  $pose->sejour_id = $sejour_id;
  $pose->operation_id = $operation_id;
  $pose->date = "now";
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("pose", $pose);
$smarty->assign("operateurs", $operateurs);

$smarty->display("inc_edit_pose_disp_vasc");
