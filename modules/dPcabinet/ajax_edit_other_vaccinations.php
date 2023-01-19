<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$injection_id    = CView::get("injection_id", "ref class|CInjection");
$patient_id      = CView::getRefCheckEdit("patient_id", "ref class|CPatient");
$recall_age      = CView::get("recall_age", "num");
$types           = (array) CView::get("types", array("str", "default" => array()));
$label_read_only = CView::get("label_read_only", "str");

CView::checkin();

$injection = CInjection::findOrNew($injection_id);
if (!$injection->_id && $types) {
  $injection->recall_age = $recall_age;
  $vaccination = new CVaccination();
  $vaccination->type = $types[0];
  $injection->_ref_vaccinations = array($vaccination);
}
else {
  $injection->loadRefVaccinations();
}
if (!$injection->_id) {
    $injection->injection_date = CMbDT::dateTime();
}
$vaccinated = ($injection->speciality != "N/A" || $injection->batch != "N/A");

$patient = CPatient::findOrFail($patient_id);

$smarty = new CSmartyDP();

$smarty->assign("injection", $injection);
$smarty->assign("patient", $patient);
$smarty->assign("recall_age", $recall_age);
$smarty->assign("vaccinated", $vaccinated);
$smarty->assign("repeat", null);

$smarty->display("vaccination/inc_edit_vaccination");
