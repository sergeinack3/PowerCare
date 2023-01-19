<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::check();
$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
    CAppUI::notFound($object_guid);
}
CView::checkin();
CView::enableSlave();

$object->needsRead();

// Look for view options
$options = CMbArray::filterPrefix($_GET, "view_");

$object->loadView();

// If no template is defined, use generic
$template = $object->makeTemplatePath("view");
if (!is_file("modules/{$template}")) {
    $template = $object instanceof CMbObject ? "system/templates/CMbObject_view.tpl" : "system/templates/CStoredObject_view.tpl";
}

// Création du template
$smarty = new CSmartyDP();
// Options
foreach ($options as $key => $value) {
    $smarty->assign($key, $value);
}
$smarty->assign("object", $object);
$smarty->display(__DIR__ . "/../$template");
