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
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Dmp\CDMPDocument;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\SIHCabinet\CReceiverHL7v2SIHCabinet;
use Ox\Mediboard\Files\CDocumentItem;

CCanDo::checkRead();

$docItem_guid   = CView::get("docItem_guid", "guid class|CDocumentItem");
$receivers_guid = CView::get("receivers", "str");
CView::checkin();

/** @var CDocumentItem $docItem */
$docItem = CMbObject::loadFromGuid($docItem_guid);
$docItem->loadRefAuthor();
$patient = $docItem->loadRelPatient();

$smarty = new CSmartyDP();
$smarty->assign('docItem'  , $docItem);
$smarty->assign('patient'  , $patient);

$receivers = array();
$count_receivers = 0;
if ($receivers_guid) {
  foreach ($receivers_guid as $_receiver_guid) {
    $module_name = null;

    /** @var CInteropReceiver $receiver */
    $receiver = CMbObject::loadFromGuid($_receiver_guid);

    // Receiver Zepra/Sisra
    if (CModule::getActive("sisra") && $receiver instanceof CReceiverHL7v3 && $receiver->type == "ZEPRA") {
      $module_name = "sisra";
    }
    // Receiver DMP
    elseif (CModule::getActive("dmp") && $receiver instanceof CReceiverHL7v3 && $receiver->type == "DMP") {
      $module_name = "dmp";

      $document_dmp = new CDMPDocument();
      $document_dmp->setObject($docItem);
      $document_dmp->loadMatchingObject("created_datetime desc");

      $smarty->assign('document_dmp', $document_dmp);
    }
    // Receiver XDS
    elseif (CModule::getActive("xds") && $receiver instanceof CReceiverHL7v3 && $receiver->type == "standard") {
      // todo ajouter les détails associés
    }
    // Receiver AppFine
    elseif (CModule::getActive("appFineClient") && $receiver instanceof CReceiverHL7v2 && $receiver->OID == CAppUI::conf("appFineClient OID_appFine")) {
      $module_name = "appFineClient";
    }
    // Receiver TAMM SIH
    elseif (CModule::getActive("oxSIHCabinet") && $receiver instanceof CReceiverHL7v2 && $receiver->_configs['sih_cabinet_id']) {
      $module_name = "oxSIHCabinet";
    }
    // Receiver SIH TAMM
    elseif (CModule::getActive("oxCabinetSIH") && $receiver instanceof CReceiverHL7v2 && $receiver->_configs['cabinet_sih_id']) {
        $module_name = "oxCabinetSIH";
    }

    if ($module_name) {
      $count_receivers++;

      $receivers[$module_name][] = array(
        "receiver"           => $receiver,
        "document_reference" => $docItem->loadDocumentReferenceActor($receiver)
      );
    }
  }
}

$smarty->assign('receivers'      , $receivers);
$smarty->assign('count_receivers', $count_receivers);
$smarty->display('inc_vw_share_document_details.tpl');
