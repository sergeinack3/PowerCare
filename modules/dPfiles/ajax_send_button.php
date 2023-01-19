<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkEdit();

$object_class = null;
$object_id    = null;
$object_guid  = CView::get("item_guid", "str default|" . "$object_class-$object_id");

$_doc_item = CMbObject::loadFromGuid($object_guid);

if (!$_doc_item || !$_doc_item->_id) {
    CAppUI::notFound($object_guid);
}

$onComplete = CView::get("onComplete", "str");

CView::checkin();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("notext", "");
$smarty->assign("_doc_item", $_doc_item);
$smarty->assign("onComplete", $onComplete);

$smarty->display("inc_file_send_button.tpl");

