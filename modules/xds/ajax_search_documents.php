<?php
/**
 * @package Mediboard\Dmp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Xds\CXDSFile;
use Ox\Interop\Xds\CXDSRequest;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$patient_id        = CView::get("patient_id", "ref class|CPatient");
$receiver_hl7v3_id = CView::get("receiver_hl7v3_id", "ref class|CReceiverHL7v3");

$user = CMediusers::get();

$patient = new CPatient();
$patient->load($patient_id);

if (!$patient->_id) {
  CAppUI::stepAjax("CPatient.none", UI_MSG_ERROR);
}

$receiver_hl7v3 = (new CInteropActorFactory())->receiver()->makeHL7v3();
$receiver_hl7v3->load($receiver_hl7v3_id);

if (!$receiver_hl7v3->_id) {
  CAppUI::stepAjax("CReceiverHL7v3.none", UI_MSG_ERROR);
}

$patient->loadIPP($receiver_hl7v3->group_id);

$search_filter = new CXDSFile();
$search_filter->setPatient($patient);

$xds_query = $search_filter->getQuery($_REQUEST, $receiver_hl7v3);

$request = CXDSRequest::sendEventRegistryStoredQuery($receiver_hl7v3, $xds_query);

CView::setSession("XDS_consultation_search", $xds_query->query);

CView::checkin();

return;

CAppUI::stepAjax("1ère étape : Recherche générique sur le DMP");

$smarty = new CSmartyDP();
$smarty->assign("patient_id", $patient->_id);
$smarty->assign("data", $request);
$smarty->display("inc_send_data_consultation.tpl");
