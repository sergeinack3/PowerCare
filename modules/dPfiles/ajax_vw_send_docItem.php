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
use Ox\Interop\Dmp\CDMPDocument;
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Files\CDocumentItem;

CCanDo::checkRead();

$docItem_guid  = CView::get("docItem_guid", "guid class|CDocumentItem");
$receiver_guid = CView::get("receiver_guid", "guid class|CInteropActor");
$module_name   = CView::get("module_name", "str");
$step          = CView::get("step", "num");
$total         = CView::get("total", "num");
CView::checkin();

/** @var CInteropActor $receiver */
$receiver = CMbObject::loadFromGuid($receiver_guid);

/** @var CDocumentItem $docItem */
$docItem = CMbObject::loadFromGuid($docItem_guid);
$docItem->loadRefAuthor();
$patient = $docItem->loadRelPatient();

$docItem->loadRefLastFileTraceability($receiver);

if ($module_name == "dmp") {
    $docItem->checkSynchroDMP($receiver);
    $docItem->loadRefLastDMPDocument();
}

if ($module_name == "sisra") {
    $docItem->checkSynchroSisra($receiver);
}

if ($module_name == "appFineClient") {
    $docItem->checkSynchroAppFine($receiver);
}

if ($module_name == "oxSIHCabinet") {
    $docItem->checkSynchroSIHCabinet($receiver);
}

if ($module_name == "oxCabinetSIH") {
    $docItem->checkSynchroCabinetSIH($receiver);
}

$smarty = new CSmartyDP("modules/$module_name");
$smarty->assign('module_name', $module_name);
$smarty->assign('docItem', $docItem);
$smarty->assign('receiver', $receiver);
$smarty->assign('patient', $patient);
$smarty->assign('total', $total);
$smarty->assign('step', $step);
$smarty->display('inc_send_doc_item.tpl');
