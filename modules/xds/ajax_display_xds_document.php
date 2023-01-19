<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Interop\Xds\CXDSRequest;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CValue::get("patient_id");
$oid        = CValue::get("oid");
$repository = CValue::get("repository_id");

$patient = new CPatient();
$patient->load($patient_id);
if (!$patient->_id) {
    CAppUI::stepAjax("Le patient n'a pas été retrouvé", UI_MSG_ERROR);
}

$receiver_hl7v3 = CXDSRequest::getDocumentRegistry();
$receiver_hl7v3 = is_array($receiver_hl7v3) ? reset($receiver_hl7v3) : $receiver_hl7v3;

if (!$receiver_hl7v3 || !$receiver_hl7v3->_id) {
    CAppUI::stepAjax("Aucun destinataire configuré", UI_MSG_ERROR);
}

$request = CXDSRequest::sendEventRetrieveDocumentSetRequest($receiver_hl7v3, $patient, $repository, $oid);
CApp::log('xds display xds document', $request);
