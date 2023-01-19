<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Dmp\CDMPRequest;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\SIHCabinet\CReceiverHL7v2SIHCabinet;
use Ox\Interop\Sisra\CSisraRequest;
use Ox\Interop\Sisra\CSisraTools;
use Ox\Interop\Xds\CXDSRequest;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategoryToReceiver;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$docItem_guid = CView::get("docItem_guid", "guid class|CDocumentItem");
CView::checkin();

/** @var CDocumentItem $docItem */
$docItem = CMbObject::loadFromGuid($docItem_guid);
if (!$docItem->_id) {
  CAppUI::stepAjax("CDocumentItem-msg-The document was not found", UI_MSG_ERROR);
}
$target = $docItem->loadTargetObject();
$docItem->countSynchronizedRecipients();

$receivers = CFilesCategoryToReceiver::getAvailableReceivers($docItem);
$count_receivers = 0;
foreach ($receivers as $module_name => $_receivers_module) {
  /** @var CInteropReceiver $_receiver */
  foreach ($_receivers_module as $_receiver) {
    $count_receivers++;
    $_receiver->_ref_file_traceability = $docItem->loadRefLastFileTraceability($_receiver);
    if ($_receiver->_class == "CReceiverHL7v3" && $_receiver->type == "DMP") {
      $docItem->checkSynchroDMP($_receiver);
    }
    if ($_receiver->_class == "CReceiverHL7v3" && $_receiver->type == "ZEPRA") {
      $docItem->checkSynchroSisra($_receiver);
    }
    if ($_receiver->_class == "CReceiverHL7v2") {
      $_receiver->loadConfigValues();
      if (CModule::getActive("appFineClient") && $_receiver->_configs["send_evenement_to_mbdmp"] ) {
        $docItem->checkSynchroAppFine($_receiver);
      }
      if (CModule::getActive("oxSIHCabinet")) {
        $docItem->checkSynchroSIHCabinet($_receiver);
      }
        if (CModule::getActive("oxCabinetSIH")) {
            $docItem->checkSynchroCabinetSIH($_receiver);
        }
    }
  }
}

$smarty = new CSmartyDP();
$smarty->assign('docItem'        , $docItem);
$smarty->assign('receivers'      , $receivers);
$smarty->assign('count_receivers', $count_receivers);
$smarty->display('inc_vw_share_document.tpl');
