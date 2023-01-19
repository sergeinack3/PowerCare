<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\FormComponentInterface;

$object_guid = CValue::get("object_guid");
$values      = CValue::get("_v", []); // pre-filled values

if (!$object_guid) {
    CAppUI::stepAjax("Un identifiant d'objet doit être fourni", UI_MSG_WARNING);

    return;
}

$object = CMbObject::loadFromGuid($object_guid);

if ($object && $object->_id) {
    global $can;
    $can->read = $object->canRead();
    $can->edit = $object->canEdit();
    $can->needsRead();

    if (
        CModule::getActive('forms')
        && CExClass::inHermeticMode(true)
        && $object instanceof FormComponentInterface
    ) {
        $can->needsEdit();
    }
}

if (!$object->_id && !empty($values)) {
    foreach ($values as $_key => $_value) {
        $object->$_key = $_value;
    }
}

$template = $object->getTypedTemplate("edit");

$object->loadEditView();
$object->loadRefsTagItems();

$check_fields = [];
if ($object instanceof CExClass) {
    $check_fields = $object->checkFields();
}

$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("check_fields", $check_fields);
$smarty->display($template);
