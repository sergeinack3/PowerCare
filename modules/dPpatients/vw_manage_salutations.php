<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;

CCanDo::checkEdit();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$owner_id     = CView::get("owner_id", "ref class|CMediusers");

CView::checkin();

if (!$owner_id && (!$object_class || !$object_id)) {
  CAppUI::stepAjax("common-error-Missing parameter", UI_MSG_ERROR);
}

if (!$object_class || !in_array(CStoredObject::class, class_parents($object_class))) {
  CAppUI::stepAjax("common-error-Invalid class name", UI_MSG_ERROR);
}

$object = $owner_id ? new $object_class() : CStoredObject::loadFromGuid("{$object_class}-{$object_id}");
if (!$object || (!$owner_id && !$object->_id)) {
  CAppUI::stepAjax("common-error-Invalid object", UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("owner_id", $owner_id);
$smarty->display("vw_manage_salutations");
