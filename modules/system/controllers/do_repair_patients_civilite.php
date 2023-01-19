<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$step = CView::post('step', 'num default|100');
$continue = CView::post('continue', 'bool default|0');

CView::checkin();

$patient = new CPatient();
$ds = $patient->getDS();

$query = new CRequest();
$query->addSelect(array('patient_id'));
$query->addTable('patients');
$query->addWhere(
  array(
    "(sexe = 'm' AND civilite IN ('mme', 'mlle')) OR (sexe = 'f' AND civilite = 'm')"
  )
);
$query->setLimit("$step");

$result = $ds->loadList($query->makeSelect());
$patient_ids = CMbArray::pluck($result, 'patient_id');

$patients = $patient->loadAll($patient_ids);

foreach ($patients as $_patient) {
  $_patient->civilite = "guess";
  if ($msg = $_patient->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::setMsg('mod-system-repair-civilite-ok', UI_MSG_OK);
  }
}

if (count($result) < $step) {
  CAppUI::setMsg('mod-system-repair-civilite-end', UI_MSG_OK);
}

echo CAppUI::getMsg();

if ($continue && count($patients) === $step) {
  CAppUI::js('repairPatientsCivilite()');
}
