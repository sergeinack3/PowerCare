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
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;

CCanDo::checkEdit();

$antecedent_ids            = CView::post('antecedent_ids', 'str');
$dossier_medical_id        = CView::post('dossier_medical_id', 'ref class|CDossierMedical');
$dossier_medical_sejour_id = CView::post('dossier_medical_sejour_id', 'ref class|CDossierMedical');
$codes_cim10               = CView::post('codes_cim10', 'str');
$codes_cim10_sejour        = CView::post('codes_cim10_sejour', 'str');

CView::checkin();

if (!$codes_cim10) {
  $codes_cim10 = array();
}

if (!$codes_cim10_sejour) {
  $codes_cim10_sejour = array();
}

foreach ($antecedent_ids as $_antecedent_id) {
  /** @var CAntecedent $_antecedent */
  $_antecedent = CMbObject::loadFromGuid("CAntecedent-$_antecedent_id");

  if ($msg = $_antecedent->delete()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
  }
}

/** @var CDossierMedical $dossier_medical */
$dossier_medical = CMbObject::loadFromGuid("CDossierMedical-$dossier_medical_id");
$dossier_medical->updateFormFields();

foreach ($codes_cim10 as $_code) {
  if (in_array($_code, $dossier_medical->_codes_cim)) {
    $key = array_search($_code, $dossier_medical->_codes_cim);
    unset($dossier_medical->_codes_cim[$key]);
  }
}

$dossier_medical->codes_cim = implode('|', $dossier_medical->_codes_cim);

if ($msg = $dossier_medical->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
  echo CAppUI::getMsg();
  CApp::rip();
}

/** @var CDossierMedical $dossier_medical_sejour */
$dossier_medical_sejour = CMbObject::loadFromGuid("CDossierMedical-$dossier_medical_sejour_id");
$dossier_medical_sejour->updateFormFields();

if ($dossier_medical_sejour->_id) {
  foreach ($codes_cim10_sejour as $_code) {
    if (in_array($_code, $dossier_medical_sejour->_codes_cim)) {
      $key = array_search($_code, $dossier_medical_sejour->_codes_cim);
      unset($dossier_medical_sejour->_codes_cim[$key]);
    }
  }

  $dossier_medical_sejour->codes_cim = implode('|', $dossier_medical_sejour->_codes_cim);

  if ($msg = $dossier_medical_sejour->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
    echo CAppUI::getMsg();
    CApp::rip();
  }
}

CAppUI::setMsg('Elements supprimés', UI_MSG_OK);
echo CAppUI::getMsg();