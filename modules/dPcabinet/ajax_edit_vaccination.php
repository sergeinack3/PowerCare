<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CRecallVaccin;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Cabinet\Vaccination\CVaccinRepository;
use Ox\Mediboard\Patients\CPatient;

$injection_id = CView::get("injection_id", "ref class|CInjection");
$patient_id   = CView::getRefCheckEdit("patient_id", "ref class|CPatient");
$types        = (array)CView::get("types", ["prop" => "str", "default" => []]);
$recall_age   = CView::get("recall_age", "str");
$repeat       = CView::get("repeat", "num");
$view         = CView::get("view", 'bool default|0');

CView::checkin();

($repeat == 0) ? CCanDo::checkEdit() : CCanDo::checkRead();

$injection             = CInjection::findOrNew($injection_id);
if (!$injection_id) {
    $injection->recall_age     = $recall_age;
    $injection->injection_date = CMbDT::dateTime();
} else {
    $recall_age = $injection->recall_age;
}
$recall_string_date    = (CRecallVaccin::makeRecallVaccine($recall_age))->getStringDates();
$vaccs_rep             = new CVaccinRepository();

$injection->loadRefVaccinations();
if ($injection->_ref_vaccinations) {
  foreach ($injection->_ref_vaccinations as $_vaccination) {
    $_vaccination->loadRefVaccine();
  }
}
else {
  $vaccinations = [];
  foreach ($types as $_type) {
    $vaccination               = new CVaccination();
    $vaccination->type         = $_type;
    $vaccination->_ref_vaccine = $vaccs_rep->findByType($_type);
    $vaccinations[]            = $vaccination;
  }
  $injection->_ref_vaccinations = $vaccinations;
}

$vaccinated = ($injection->speciality != "N/A" || $injection->batch != "N/A");
if ($injection->_id) {
    $patient = CPatient::findOrFail($injection->patient_id);
} else {
    $patient = CPatient::findOrFail($patient_id);
}


$vaccines_same_recall = [];

$i_object = new CInjection();
$i_object->recall_age = $injection->recall_age;
$i_object->patient_id = $patient->_id;
$injections = $i_object->loadMatchingListEsc();

foreach ($injections as $_injection) {
  $vaccinations = $_injection->loadRefVaccinations();
  foreach (CMbArray::pluck($vaccinations, "type") as $_type) {
    if (!in_array($_type, $vaccines_same_recall)) {
      $vaccines_same_recall[] = $_type;
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("types", $types);
$smarty->assign("vaccines", $vaccs_rep->getAll());
$smarty->assign("patient", $patient);
$smarty->assign("repeat", $repeat);
$smarty->assign("label_read_only", "1");
$smarty->assign("available_items", $vaccs_rep->getAvailableTypesRecall());

if ($repeat > 0) {
  $smarty->assign("injection", $injection);
  $smarty->assign("recall_age", $recall_age);
  $smarty->assign("vaccinated", $vaccinated);

  $smarty->display("vaccination/multiple_vaccinations");
}
else {
  $smarty->assign("injection", $injection);
  $smarty->assign("recall_string_date", $recall_string_date);
  $smarty->assign("vaccinated", $vaccinated);
  $smarty->assign("vaccines_same_recall", $vaccines_same_recall);
  $smarty->assign("view", $view);
  $smarty->display("vaccination/edit_vaccination");
}
