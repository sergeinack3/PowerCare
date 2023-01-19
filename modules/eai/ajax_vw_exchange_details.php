<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Dicom\CExchangeDicom;
use Ox\Interop\Eai\CEchangeXML;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Hl7\CExchangeHL7v2;

/**
 * View exchange details
 */
CApp::setTimeLimit(240);
CApp::setMemoryLimit("1024M");

CCanDo::checkRead();

$exchange_guid = CValue::get("exchange_guid");

$observations = $doc_errors_msg = $doc_errors_ack = array();

// Chargement de l'échange demandé
/** @var CExchangeDataFormat $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);

$exchange->loadRefs();
$exchange->loadRefsInteropActor();
$exchange->getErrors();
$exchange->getObservations();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("exchange", $exchange);

switch (true) {
  case $exchange instanceof CExchangeTabular:
    CMbObject::$useObjectCache = false;

    $msg_segment_group = $exchange->getMessage();

    if ($msg_segment_group) {
      $doc = $msg_segment_group->toXML();
      $msg_segment_group->_xml = $doc->saveXML();
    }

    $ack_segment_group = $exchange->getACK();

    if ($ack_segment_group) {
      $doc = $ack_segment_group->toXML();
      $ack_segment_group->_xml = $doc->saveXML();
    }

    if ($exchange instanceof CExchangeHL7v2 && $exchange->altered_content_id) {
        $smarty->assign("msg_segment_group_altered", $exchange->getMessageInitial());
    }

    $smarty->assign("msg_segment_group", $msg_segment_group);
    $smarty->assign("ack_segment_group", $ack_segment_group);
    $smarty->assign("exchange"         , $exchange);
    $smarty->display("inc_exchange_tabular_details.tpl");
    break;

  case $exchange instanceof CEchangeXML:
    $smarty->display("inc_exchange_xml_details.tpl");
    break;

  case $exchange instanceof CExchangeDicom:
    $exchange->decodeContent();
    $smarty->display("inc_exchange_dicom_details.tpl");
    break;

  default:
    $exchange->guessDataType();
    $smarty->display("inc_exchange_any_details.tpl");
    break;
}
