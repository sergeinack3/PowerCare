<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CSalutation;

CCanDo::checkEdit();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$owner_id     = CView::get("owner_id", "ref class|CMediusers");

CView::checkin();

$object = $owner_id ? new $object_class() : CStoredObject::loadFromGuid("{$object_class}-{$object_id}");

/** @var CSalutation[] $salutations */
$salutations = CSalutation::loadAllSalutations($object->_class, $object->_id, null, $owner_id);

CStoredObject::massLoadFwdRef($salutations, "owner_id");
CStoredObject::massLoadFwdRef($salutations, "object_id");

$functions               = array();
$salutations_by_function = array();
/** @var CSalutation $_salutation */
foreach ($salutations as $_salutation) {
  $_salutation->loadRefOwner();

  if (!isset($salutations_by_function[$_salutation->_ref_owner->function_id])) {
    $salutations_by_function[$_salutation->_ref_owner->function_id] = array();
    $functions[$_salutation->_ref_owner->function_id]               = $_salutation->_ref_owner->loadRefFunction();
  }

  $salutations_by_function[$_salutation->_ref_owner->function_id][] = $_salutation;
}
$salutations = $salutations_by_function;

ksort($salutations);

$smarty = new CSmartyDP();
$smarty->assign("salutations", $salutations);
$smarty->assign("functions", $functions);
$smarty->assign("owner_id", $owner_id);
$smarty->display("inc_manage_salutations.tpl");
