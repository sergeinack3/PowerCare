<?php 
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Hl7\CHL7v2Message;
use Ox\Interop\Hl7\CHL7v2MessageXML;
use Ox\Interop\Hl7\CLogModificationExchange;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CContentTabular;

$old_ipp       = CView::get("old_ipp", "str");
$new_ipp       = CView::get("new_ipp", "str");
$old_nda       = CView::get("old_nda", "str");
$new_nda       = CView::get("new_nda", "str");
$exchange_guid = CView::get("exchange_guid", "str");
CView::checkin();

/** @var CExchangeDataFormat $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);
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

// Est ce que le patient du IPP correspond au patient du NDA ?
if ($new_ipp && $new_nda) {
  $idex_patient = CIdSante400::getMatch("CPatient", $actor->_tag_patient, $new_ipp);

  /** @var CPatient $patient_found */
  $patient_IPP = CMbObject::loadFromGuid("$idex_patient->object_class-$idex_patient->object_id");

  $idex_sejour = CIdSante400::getMatch("CSejour", $actor->_tag_sejour, $new_nda);

  /** @var CSejour $admit_found */
  $admit_found = CMbObject::loadFromGuid("$idex_sejour->object_class-$idex_sejour->object_id");
  $patient_NDA = $admit_found->loadRefPatient();

  if ($patient_IPP->_id && $patient_NDA->_id && $patient_IPP->_id != $patient_NDA->_id) {
    CAppUI::setMsg("CExchangeDataFormat-msg-Patients are differents", UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
  }
}

$content_tabular = new CContentTabular();
$content_tabular->load($exchange->message_content_id);

$hl7_message = new CHL7v2Message;
$hl7_message->parse($content_tabular->content);

/** @var CHL7v2MessageXML $xml */
$xml = $hl7_message->toXML(null, true);

// Récupération du PID
$control_identifier_type_code = CValue::read($sender->_configs, "control_identifier_type_code");
if (CHL7v2Message::$handle_mode === "simple" || !$control_identifier_type_code) {
  $xml = CHL7v2Message::setIdentifier($xml, "//PID.3", $new_ipp, "CX.1");
}
else {
  $xml = CHL7v2Message::setIdentifier($xml, "//PID.3", $new_ipp, "CX.1", "CX.5", "PI");
}

// Récupération du NDA
if (CMbArray::get($actor->_configs, "handle_NDA") == "PV1_19") {
  if (CHL7v2Message::$handle_mode === "simple" || !$control_identifier_type_code) {
    $xml = CHL7v2Message::setIdentifier($xml, "//PV1.19", $new_nda, "CX.1");
  }
  else {
    $xml = CHL7v2Message::setIdentifier($xml, "//PV1.19", $new_nda, "CX.1", "CX.5", "AN");
  }
}
else {
  if (CHL7v2Message::$handle_mode === "simple" || !$control_identifier_type_code) {
    $xml = CHL7v2Message::setIdentifier($xml, "//PID.18", $new_nda, "CX.1");
  }
  else {
    $xml = CHL7v2Message::setIdentifier($xml, "//PID.18", $new_nda, "CX.1", "CX.5", "AN");
  }
}

$exchange->_message = $xml->toER7($hl7_message);
$exchange->store();

CAppUI::setMsg("CExchangeDataFormat-msg-Value modified");

$data_update = array(
  "before" => array(
    "IPP" => $old_ipp,
    "NDA" => $old_nda,
  ),
  "after" => array(
    "IPP" => $new_ipp,
    "NDA" => $new_nda,
  )
);

$log_modification_exchange                  = new CLogModificationExchange();
$log_modification_exchange->user_id         = CAppUI::$instance->user_id;
$log_modification_exchange->data_update     = json_encode($data_update);
$log_modification_exchange->content_id      = $content_tabular->_id;
$log_modification_exchange->content_class   = $content_tabular->_class;
$log_modification_exchange->datetime_update = "now";
$log_modification_exchange->store();

echo CAppUI::getMsg();