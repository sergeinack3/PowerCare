<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");

CView::checkin();

/** @var CMbObject $object */
$object = new $object_class;
$object->load($object_id);

$files = CDocumentItem::loadDocItemsByObject($object);

foreach ($files as $_files_by_cat) {
  foreach ($_files_by_cat["items"] as $_file) {
    if ($_file instanceof CCompteRendu) {
      $_file->makePDFpreview();
    }
  }
}
$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("files" , $files);

$smarty->display("inc_files_gallery.tpl");

