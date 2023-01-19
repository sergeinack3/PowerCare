<?php
/**
 * @package Mediboard\Dmp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Dmp\CDMPFile;
use Ox\Interop\Dmp\CDMPRequest;
use Ox\Interop\Dmp\CVIHF;
use Ox\Interop\Hl7\CExchangeHL7v3;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\InteropResources\valueset\CXDSValueSet;
use Ox\Interop\Xds\CXDSQueryRegistryStoredQuery;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CValue::get("patient_id");

$patient = new CPatient();
$patient->load($patient_id);
$ins = $patient->loadLastINS();

$vihf = new CVIHF(true);
$vihf->getData(true);
$vihf->patient = $patient;

$response      = CValue::post("response");
$exchange_guid = CValue::post("exchange_guid");
$event_type    = CValue::post("event_type");

$smarty = new CSmartyDP();

/** @var CExchangeHL7v3 $exchange */
$exchange = CMbObject::loadFromGuid($exchange_guid);

$exchange->send_datetime = CMbDT::dateTime();
$exchange->_acquittement = $response;
$exchange->store();
if (!$response) {
  $exchange->store();
  CAppUI::displayAjaxMsg("DMP-no_response", UI_MSG_WARNING);

  return;
}

$response_part = preg_match("#<.+Envelope>#", $response, $matches);
$body          = stripslashes(CMbArray::get($matches, 0));

//Suppression des élément Envelope et body de l'acquittement
try {
  $dom = new CMbXMLDocument();
  $dom->loadXML($body);
  $xpath = new CMbXPath($dom);
  $xpath->registerNamespace("soap", "http://schemas.xmlsoap.org/soap/envelope/");
  $node_response = $xpath->queryUniqueNode("/soap:Envelope/soap:Body/*");
  $data_ack      = $dom->saveXML($node_response);
}
catch (Exception $e) {
  $exchange->_acquittement = $response;
  $exchange->store();
  CAppUI::displayAjaxMsg("Erreur sur la réception de l'acquittement", UI_MSG_ERROR);

  return;
}

if (!$ack = CReceiverHL7v3::createAcknowledgment($event_type, $data_ack)) {
  $exchange->_acquittement = $response;
  $exchange->store();
  CAppUI::displayAjaxMsg("Erreur sur l'acquittement", UI_MSG_ERROR);

  return;
}
$status = $ack->getStatutAcknowledgment();
$valide = $ack->dom->schemafilename ?
  $ack->dom->schemaValidate() ? 1 : 0
  : 1;

$queryResponseCode = $ack->getQueryAck();

$exchange->statut_acquittement = $status;
$exchange->acquittement_valide = $valide;
$exchange->_acquittement       = $body;
$exchange->store();

$result = $ack->getQueryAck();

if ($ack->status == "Failure") {
  CAppUI::stepAjax("Problème lors de la requête", UI_MSG_ERROR);
}

$count = $ack->getResultCount();

$category_exclude = array("URN^urn:oid:1.3.6.1.4.1.19376.1.2.1.1.1" => "");

$types_document       = CDMPFile::getTypeDocument();
$categories           = CXDSValueSet::load('classCode');
$categories           = array_diff_key($categories, $category_exclude);
$categories["unknow"] = "Inconnu";


$list_files_by_cat = array_fill_keys(array_keys($categories), array());

$smarty = new CSmartyDP();
$smarty->assign("types_document", $types_document);
$smarty->assign("categories", $categories);
$smarty->assign("patient", $patient);

if ($count == 0) {
  $smarty->assign("list_documents", $list_files_by_cat);
  $smarty->display("inc_list_dmp_documents.tpl");
  CApp::rip();
}

$type_request = CValue::session("DMP_consultation_search");
switch ($type_request) {
  //FindDocuments
  case "urn:uuid:14d4debf-8f97-4251-9a74-a90016b0af0d":
    break;
  //FindSubmissionSets
  case "urn:uuid:f26abbcb-ac74-4422-8a30-edb644bbc1a9":
    CValue::setSession("DMP_consultation_search", "urn:uuid:a7ae438b-4bc2-4642-93e9-be891f7bb155");
    $query = new CXDSQueryRegistryStoredQuery();
    $query->setQueryUUID("urn:uuid:a7ae438b-4bc2-4642-93e9-be891f7bb155", $patient, CMbArray::pluck($result, "status"), "LeafClass");

    $receiver_hl7v3 = CDMPRequest::getDocumentRegistry();

    if (!$receiver_hl7v3 || ($receiver_hl7v3 && !$receiver_hl7v3->_id)) {
      CAppUI::stepAjax("Aucun destinataire configuré", UI_MSG_ERROR);
    }

    $request = CDMPRequest::sendEventRegistryStoredQuery($receiver_hl7v3, $vihf, $query);
    CAppUI::stepAjax("Etape 2 sur 3 : Recherche des associations liées aux lots de soumission");
    $smarty->assign("patient_id", $patient->_id);
    $smarty->assign("data", $request);
    $smarty->display("inc_send_data_consultation.tpl");
    break;
  //GetDocuments
  case "urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4":
    CValue::setSession("DMP_consultation_search", "");
    $list_documents = CDMPFile::transformExtrinsicObject($ack->getDocuments());

    foreach ($list_documents as $_document) {
      $_document->type_document = CMbArray::get($types_document, $_document->type_document, "Inconnu");
      if (array_key_exists($_document->category_xds, $list_files_by_cat)) {
        $list_files_by_cat[$_document->category_xds][] = $_document;
        continue;
      }
      $list_files_by_cat["unknow"][] = $_document;
    }

    $smarty->assign("list_documents", $list_files_by_cat);
    $smarty->display("inc_list_dmp_documents.tpl");
    break;
  //GetAssociations
  case "urn:uuid:a7ae438b-4bc2-4642-93e9-be891f7bb155":
    CValue::setSession("DMP_consultation_search", "urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4");
    $entryUUID = $ack->getDocumentUUIDAssociation();

    $query = new CXDSQueryRegistryStoredQuery();
    $query->setQueryUUID("urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4", $patient, $entryUUID, "LeafClass");

    $receiver_hl7v3 = CDMPRequest::getDocumentRegistry();

    if (!$receiver_hl7v3 || ($receiver_hl7v3 && !$receiver_hl7v3->_id)) {
      CAppUI::stepAjax("Aucun destinataire configuré", UI_MSG_ERROR);
    }

    $request = CDMPRequest::sendEventRegistryStoredQuery($receiver_hl7v3, $vihf, $query);
    CAppUI::stepAjax("Etape 3 sur 3 : Recherche des documents liées aux associations");
    $smarty->assign("patient_id", $patient->_id);
    $smarty->assign("data", $request);
    $smarty->display("inc_send_data_consultation.tpl");
    break;
  default:
    CAppUI::stepAjax("La requête effectué n'est pas supporté", UI_MSG_ERROR);
}

//todo ajout de la date de recherche (nécessaire pour la recherche rapide)
/*$user = CMediusers::get();

$log_user = new CDMPLogUser();
$log_user->patient_id = $patient->_id;
$log_user->user_id    = $user->_id;
$log_user->loadMatchingObject();
$log_user->last_connection = CMbDT::dateTime();

if (!$log_user->_id) {
  $log_user->first_connection = CMbDT::dateTime();
}

if ($msg = $log_user->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ALERT);
}*/
