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
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$limit = CView::get("limit", "num default|1000");

CView::checkin();

CApp::setMemoryLimit("2048M");
CApp::setTimeLimit("3600");

$patient = new CPatient();

$where = array(
  "patients.nom_soundex2" => "IS NULL",
);

$patients = $patient->loadList($where, null, $limit);

$count = 0;

foreach ($patients as $_patient) {
  if ($msg = $_patient->store()) {
    $_patient->repair();
    if ($msg = $_patient->store()) {
      CAppUI::stepAjax("Patient $_patient->_view - ID $_patient->_id - (né le " . $_patient->getFormattedValue("naissance") . ") non sauvegardé : $msg", UI_MSG_WARNING);
      continue;
    }
  }
  $count++;
}

CAppUI::stepAjax("$count / " . count($patients) . " patients resauvegardés");