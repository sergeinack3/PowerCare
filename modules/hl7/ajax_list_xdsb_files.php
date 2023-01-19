<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CReceiverHL7v3;
use Ox\Interop\Xds\CXDSFile;
use Ox\Interop\Xds\CXDSQueryRegistryStoredQuery;
use Ox\Interop\Xds\CXDSRequest;
use Ox\Interop\Xds\CXDSTools;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$patient_id = CValue::get("object_id");

/** @var CPatient $patient */
$patient = CMbObject::loadFromGuid("CPatient-$patient_id");
$patient->loadIPP();

/** @var CReceiverHL7v3 $receiver_hl7v3 */
$receiver_hl7v3 = CXDSRequest::getDocumentRegistry();
if (!$receiver_hl7v3 || !$receiver_hl7v3->_id) {
    CAppUI::stepAjax("Aucun destinataire configuré", UI_MSG_ERROR);
}

$search_filter = new CXDSFile();
$search_filter->setPatient($patient);
$search_filter->returnComposedObjects = "false";
$search_filter->returnType            = "ObjectRef";

$values = [
    "_date_max_submit" => CXDSTools::getTimeUtc(),
    "_date_min_submit" => CXDSTools::getTimeUtc("2015-07-08 11:14:15.638276"),
];

$xds_query = $search_filter->getQuery($values, $receiver_hl7v3);

// 1ère étape : Recherche générique
CAppUI::stepAjax("Étape 1 sur 3 : Recherche générique sur le registre");
$ack = CXDSRequest::sendEventRegistryStoredQuery($receiver_hl7v3, $xds_query);

$status = $ack->getStatutAcknowledgment();
if ($status == "Failure") {
    CAppUI::stepAjax("Problème lors de la requête", UI_MSG_ERROR);
}

$result = $ack->getQueryAck();

// @todo LIMIT !!!!
$result = array_slice($result, 0, 5);

CAppUI::stepAjax("Etape 2 sur 3 : Recherche des associations liées aux lots de soumission");

$query = new CXDSQueryRegistryStoredQuery();
$query->setQueryUUID(
    "urn:uuid:a7ae438b-4bc2-4642-93e9-be891f7bb155",
    $patient,
    CMbArray::pluck($result, "status"),
    "LeafClass"
);

$receiver_hl7v3 = CXDSRequest::getDocumentRegistry();
$ack            = CXDSRequest::sendEventRegistryStoredQuery($receiver_hl7v3, $query);

$status = $ack->getStatutAcknowledgment();
if ($status == "Failure") {
    CAppUI::stepAjax("Problème lors de la requête", UI_MSG_ERROR);
}

$entryUUID = $ack->getDocumentUUIDAssociation();

CAppUI::stepAjax("Etape 3 sur 3 : Recherche des documents liées aux associations");

$query = new CXDSQueryRegistryStoredQuery();
$query->setQueryUUID("urn:uuid:5c4f972b-d56b-40ac-a5fc-c8ca9b40b9d4", $patient, $entryUUID, "LeafClass");

$ack = CXDSRequest::sendEventRegistryStoredQuery($receiver_hl7v3, $query);

$list_documents = CXDSFile::transformExtrinsicObject($ack->getDocuments());
CApp::log('hl7 list xdsb files', $list_documents);
$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("list_documents", $list_documents);
$smarty->display("inc_list_xds_documents.tpl");
