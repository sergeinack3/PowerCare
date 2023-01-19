<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\System\CHistoryViewer;

CCanDo::checkAdmin();

if (!CAppUI::pref('system_show_history')) {
    CAppUI::accessDenied();
}

$object_class = CValue::get("object_class");
$object_id    = CValue::get("object_id");

/** @var string[] $instances */
$declaring_classes = [];

/** @var CStoredObject $object */
$object = new $object_class();
$object->load($object_id);

if (!$object instanceof CStoredObject) {
    CAppUI::stepAjax("common-error-Object not a CStoredObject", UI_MSG_ERROR);

    return;
}

$deepness = 2;

$tree = [
    "back" => [],
    "fwd"  => [],
];
CHistoryViewer::makeTree($tree, $deepness, $object);

$smarty = new CSmartyDP();

$smarty->assign("tree", $tree);
$smarty->assign("object", $object);

$smarty->display("view_full_history.tpl");
