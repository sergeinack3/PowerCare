<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CSalutation;

$salutation_id = CView::get("salutation_id", "ref class|CSalutation");
$object_class  = CView::get("object_class", "str");
$object_id     = CView::get("object_id", "ref class|$object_class");
$owner_id      = CView::get("owner_id", "ref class|CMediusers");

CView::checkin();

$salutation = new CSalutation();

if (!$salutation->load($salutation_id)) {
  if (!$object_class || !in_array(CStoredObject::class, class_parents($object_class))) {
    CAppUI::stepAjax("common-error-Invalid class name", UI_MSG_ERROR);
  }

  if ($owner_id) {
    $salutation->owner_id = $owner_id;
    $salutation->object_class = $object_class;
  }
  else {
    $object = CStoredObject::loadFromGuid("{$object_class}-{$object_id}");

    if (!$object || !$object->_id) {
      CAppUI::stepAjax("common-error-Invalid object", UI_MSG_ERROR);
    }

    $salutation->owner_id     = CMediusers::get()->_id;
    $salutation->object_class = $object_class;
    $salutation->object_id    = $object_id;
  }
}

$salutation->loadRefOwner();
$salutation->loadTargetObject();

$smarty = new CSmartyDP();
$smarty->assign("salutation", $salutation);
$smarty->assign("object_class", $object_class);
$smarty->assign("owner_id", $owner_id);
$smarty->display("vw_edit_salutation");
