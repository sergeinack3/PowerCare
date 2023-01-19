<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CDocumentItem;

CCanDo::checkEdit();

$context_guid = CView::get("context_guid", "str");

CView::checkin();

/** @var CMbObject $context */
$context = CMbObject::loadFromGuid($context_guid);

if (!$context->canDo()->read) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Vous n'avez pas accès en lecture à cet élément");
  CApp::rip();
}

$context->loadRefsFiles();

$images = array();

foreach ($context->_ref_files as $_file) {
  // Seulement les images
  if (strpos($_file->file_type, "image") === 0) {
    CDocumentItem::makeIconName($_file);
    $images[] = $_file;
  }
}

$smarty = new CSmartyDP();

$smarty->assign("context", $context);
$smarty->assign("images" , $images);

$smarty->display("inc_list_images");