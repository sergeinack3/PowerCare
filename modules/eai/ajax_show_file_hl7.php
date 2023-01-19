<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CContentTabular;

$exchange_guid = CView::get("exchange_guid", "str");
CView::checkin();

// Chargement de l'échange demandé
/** @var CExchangeDataFormat $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);

$exchange->loadRefs();
$exchange->loadRefsInteropActor();
$exchange->getErrors();
$exchange->getObservations();

$limit_size = 100;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange", $exchange);

switch (true) {
  case $exchange instanceof CExchangeTabular:
    CMbObject::$useObjectCache = false;

    $exchange->loadRefsInteropActor();
    if ($exchange->receiver_id) {
      /** @var CInteropReceiver $actor */
      $actor = $exchange->_ref_receiver;
      $actor->loadConfigValues();
    }
    else {
      /** @var CInteropSender $actor */
      $actor = $exchange->_ref_sender;
      $actor->getConfigs($exchange);
    }

    $content_tabular = new CContentTabular();
    $content_tabular->load($exchange->message_content_id);

    $hl7_message = new CHL7v2Message;
    $hl7_message->parse($content_tabular->content);

    /** @var CHL7v2MessageXML $xml */
    $xml = $hl7_message->toXML(null, false);

    $files_guid_ED = CHL7v2Message::getMultipleValues($xml, "//OBX", "OBX.5", null, null, 5, "^");
    $files_guid_RP = CHL7v2Message::getMultipleValues($xml, "//OBX", "OBX.5", null, null, 1, "^");
    $files_guid = array_merge($files_guid_ED, $files_guid_RP);

    $files = array();
    foreach ($files_guid as $_file_guid) {
      if (!$_file_guid) {
        continue;
      }

      $guid = explode('-', $_file_guid);

      if (!CMbArray::get($guid, 0) || !CMbArray::get($guid, 1)) {
        continue;
      }

      if (!class_exists(CMbArray::get($guid, 0))) {
        continue;
      }

      /** @var CFile $file */
      $file = CMbObject::loadFromGuid($_file_guid);

      if (!$file) {
        continue;
      }

      $file->canDo();
      $object = $file->loadTargetObject();
      if (CModule::getActive("appFineClient")) {
        if ($object instanceof CSejour) {
          CAppFineClient::loadIdex($file, $object->group_id);
          CAppFineClient::loadIdex($object, $object->group_id);
        }
        else {
          CAppFineClient::loadIdex($file);
          CAppFineClient::loadIdex($object);
        }
      }

      $files[] = $file;
    }

    $smarty->assign("files", $files);
    $smarty->display("inc_show_file_hl7.tpl");
    break;
}
