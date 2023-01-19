<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Patients\CPatient;

$patient_id          = CValue::get("patient_id");
$administrative_data = CValue::get('administrative_data', 0);
$patient             = new CPatient();
$patient->load($patient_id);

$cv = CFseFactory::createCV();

if ($cv) {
  if ($patient->_id) {
    $cv->getPropertiesFromVitale($patient, $administrative_data);
  }
  else {
    $cv->getPropertiesFromVitale($patient);
  }

  $msg = $patient->store();

  if ($msg) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
}