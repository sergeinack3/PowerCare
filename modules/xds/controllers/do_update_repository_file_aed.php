<?php
/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Xds\CXDSDocument;
use Ox\Interop\Xds\CXDSQueryRegistryStoredQuery;
use Ox\Interop\Xds\CXDSRequest;
use Ox\Interop\Xds\CXDSTools;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CPatient;

$document_guid = CValue::post("document_guid");
$hide          = CValue::post("xds_file_hide");

/** @var CDocumentItem $docItem */
$docItem = CMbObject::loadFromGuid($document_guid);

$object = $docItem->loadTargetObject();
if ($object instanceof CConsultAnesth) {
  $object = $object->loadRefConsultation();
}
if ($object instanceof CPatient) {
  CAppUI::setMsg("Impossible d'ajouter un document lié directement à un patient", UI_MSG_ERROR);
  CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg());
  CApp::rip();
}

$document_xds = new CXDSDocument();
$document_xds->getLastSend($docItem->_id, $docItem->_class);
$patient = $object->loadRefPatient();

$uuid = null;

if ($document_xds->_id) {
  $new_version = $docItem->_version;

  // Le document est dépublié, nous ne pouvons rien faire
  if ($document_xds->etat === "DELETE") {
    CAppUI::setMsg("Ce document a été dépublié", UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }

  if ($new_version === $document_xds->version) {
    CAppUI::setMsg("Cette version du document est déjà présente sur l'entrepôt de documents", UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }

  // Chargement d'un destinataire HL7v3
  $receiver_hl7v3 = CXDSRequest::getDocumentRegistry();
    $receiver_hl7v3 = is_array($receiver_hl7v3) ? reset($receiver_hl7v3) : $receiver_hl7v3;
  if (!$receiver_hl7v3 || !$receiver_hl7v3->_id) {
    CAppUI::setMsg("Aucun destinataire XDS configuré", UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }

  // Besoin de générer l'ancien OID du document pour la requête d'existence du document
  // Objet est obligatoirement un compte rendu(cfile version === 1)

  /** @var CCompteRendu $docItem */
  $docItem->version = $document_xds->version;
  try {
    $xds_query = new CXDSQueryRegistryStoredQuery();
    $xds_query->setEntryIDbyDocument($docItem, $receiver_hl7v3);

    $result = CXDSRequest::sendEventRegistryStoredQuery(
      $receiver_hl7v3,
      $xds_query
    );
  }
  catch (SoapFault $e) {
    CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }
  catch (CMbException $e) {
    CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }

  //Communication impossible
  if ($result === null) {
    CAppUI::setMsg("Communication impossible", UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }

  //Aucun retour
  if ($result === "") {
    CAppUI::setMsg("Aucun retour", UI_MSG_ERROR);
    CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
    CApp::rip();
  }

  $ack = $result->getQueryAck();
  $msg = UI_MSG_OK;
  foreach ($ack as $_ack) {
    if ($_ack["context"]) {
      CAppUI::setMsg($_ack["context"], UI_MSG_ERROR);
      CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
      CApp::rip();
    }

    $uuid = $_ack["status"];

    //Le document n'est pas présent ou qu'il est archivé (ce n'est pas un UUID standard)
    if ($uuid === "Success") {
      CAppUI::setMsg("Le document n'est pas présent sur l'entrepôt de dcouments, ou est archivé", UI_MSG_ERROR);
      CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
      CApp::rip();
    }
  }

  $docItem->version = $new_version;
}

// Chargement d'un destinataire HL7v3
$receiver_hl7v3 = CXDSRequest::getDocumentRepository();
$receiver_hl7v3 = is_array($receiver_hl7v3) ? reset($receiver_hl7v3) : $receiver_hl7v3;

if (!$receiver_hl7v3 || ($receiver_hl7v3 && !$receiver_hl7v3->_id)) {
  CAppUI::setMsg("Aucun destinataire XDS configuré", UI_MSG_ERROR);
  CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg(true));
  CApp::rip();
}

try {
  $result = CXDSRequest::sendEventProvideAndRegisterDocumentSetRequest(
    $receiver_hl7v3,
    $docItem,
    $document_xds,
    $hide,
    $uuid
  );
}
catch (SoapFault $e) {
  CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
  CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg());
  CApp::rip();
}
catch (CMbException $e) {
  CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
  CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg());
  CApp::rip();
}

if (!$result) {
  CAppUI::setMsg("Aucune source pour cet acteur / Source non active", UI_MSG_ERROR);
  CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg());
  CApp::rip();
}

if ($result === "") {
  CAppUI::setMsg("Aucun acquittement", UI_MSG_ERROR);
  CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg());
  CApp::rip();
}

$ack = $result->getQueryAck();
$msg = UI_MSG_OK;

foreach ($ack as $_ack) {
  if (CMbArray::get($_ack, "context")) {
    $msg = UI_MSG_ERROR;
  }

  CAppUI::setMsg(CMbArray::get(CXDSTools::$error, CMbArray::get($_ack, "context")) . " : " . CMbArray::get($_ack, "status"), $msg);
}

if ($msg === UI_MSG_OK) {
  $document_xds->_id        = null;
  $document_xds->date       = "now";
  $document_xds->etat       = "CREATE";
  $document_xds->visibilite = $hide;
  $document_xds->patient_id = $patient->_id;
  $document_xds->version    = $docItem->_version;
  if ($uuid) {
    $document_xds->etat = "RPLC";
  }
  if ($msg = $document_xds->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
}

CAppUI::callbackAjax('window.parent.SystemMessage.notify', CAppUI::getMsg());

CApp::rip();
