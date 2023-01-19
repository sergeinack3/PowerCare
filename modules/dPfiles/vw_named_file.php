<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str", true);
$object_id    = CView::get("object_id", "ref class|$object_class", true);
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id", true);

$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
    CAppUI::notFound($object_guid);
}

$name = CView::get("name", "str");
$size = CView::get("size", "num");
$mode = CView::get("mode", "str");

CView::checkin();

$object->loadNamedFile($name);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("name", $name);
$smarty->assign("size", $size);
$smarty->assign("mode", $mode);

$smarty->display("inc_named_file.tpl");
