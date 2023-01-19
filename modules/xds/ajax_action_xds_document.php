<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Xds\CXDSDocument;
use Ox\Mediboard\Files\CDocumentItem;

CCanDo::check();

$document_guid = CView::get("document_guid", "guid class|CDocumentItem");
CView::checkin();

if (!$document_guid) {
  CAppUI::stepAjax("CDocumentItem.none", UI_MSG_ERROR);
}

/** @var CDocumentItem $document_item */
$document_item = CMbObject::loadFromGuid($document_guid);
$patient       = $document_item->loadRelPatient();

$xds_document = new CXDSDocument();
$xds_document->setObject($document_item);
$xds_document->loadMatchingObject("date desc");

$smarty = new CSmartyDP();
$smarty->assign("document_item", $document_item);
$smarty->assign("xds_document", $xds_document);
$smarty->display("inc_action_xds_document.tpl");