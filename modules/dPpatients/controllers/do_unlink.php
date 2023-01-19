<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CValue::post('patient_id');

$patient = new CPatient();
if (!$patient->load($patient_id)) {
  CAppUI::stepAjax("Chargement impossible du patient", UI_MSG_ERROR);
}

$patient->patient_link_id = "";
if ($msg = $patient->store()) {
  CAppUI::stepAjax("Association du patient impossible : $msg", UI_MSG_ERROR);
}

CAppUI::stepAjax("$patient->_view désassocié");

CApp::rip();
