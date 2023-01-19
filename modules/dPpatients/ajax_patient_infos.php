<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$patient_id = CView::get("patient_id", "ref class|CPatient", true);
CView::checkin();
CView::enableSlave();

$group = CGroups::loadCurrent();

$patient = new CPatient();
$patient->load($patient_id);

$patient->loadIPP();

$sejours  = $patient->loadRefsSejours();
$consults = $patient->loadRefsConsultations();

$datas_patient                  = array();
$datas_patient["IPP"]           = $patient->_ref_IPP->id400;
$datas_patient["naissance"]     = CMbDT::format($patient->naissance, CAppUI::conf('date'));
$datas_patient["phone"]         = array(
  "tel"              => $patient->tel,
  "tel2"             => $patient->tel2,
  "tel_autre"        => $patient->tel_autre,
  "tel_autre_mobile" => $patient->tel_autre_mobile
);
$datas_patient["sejours"]       = array();
$datas_patient["consultations"] = array();
$datas_patient["adresse"]       = $patient->adresse . " " . $patient->cp . " " . $patient->ville;

krsort($sejours);

CStoredObject::massLoadFwdRef($sejours, "group_id");
$plage_consults = CStoredObject::massLoadFwdRef($consults, "plageconsult_id");
$praticiens     = CStoredObject::massLoadFwdRef($plage_consults, "chir_id");
$functions      = CStoredObject::massLoadFwdRef($praticiens, "function_id");
CStoredObject::massLoadFwdRef($functions, "group_id");

foreach ($sejours as $_sejour) {
  $group_sejour = $_sejour->loadRefEtablissement();

  if ($group_sejour->_id == $group->_id) {
    $datas_patient["sejours"][$_sejour->_id] = $_sejour->_view;
  }
}

foreach ($consults as $_consult) {
  $group_consult = $_consult->loadRefGroup();

  if ($group_consult->_id == $group->_id) {
    $datas_patient["consultations"][$_consult->_id] =
        CAppUI::tr(
            'CConsultation-Consultation of %s - %s-court',
            $_consult->loadRefPlageConsult()->_ref_chir->_view,
            CMbDT::format($_consult->loadRefPlageConsult()->date, CAppUI::conf("date"))
        );
  }
}

CApp::json($datas_patient);
