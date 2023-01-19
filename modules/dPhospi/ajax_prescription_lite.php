<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id    = CValue::get("sejour_id");
$user_id      = CValue::get("user_id");
$object_class = CValue::get("object_class");
$object_id    = CValue::get("object_id", 0);

$sejour = new CSejour;
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPrescriptionSejour();

if ($object_class && $object_id) {
  $object = new $object_class;
  $object->load($object_id);
}

$smarty = new CSmartyDP;

$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("user_id", $user_id);
$smarty->assign("prescription", $sejour->_ref_prescription_sejour);
$smarty->assign("object_id", $object_id);
if ($object_id) {
  $smarty->assign("object", $object);
}
$smarty->display("inc_prescription_lite.tpl");
