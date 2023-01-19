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

CCando::checkEdit();

$injection_id = CView::get("injection_id", "ref class|CInjection");
$patient = CView::getRefCheckEdit("patient_id", "ref class|CPatient");
$type = CView::get("type", "str");

CView::checkin();

$injection = CInjection::findOrNew($injection_id);


$repeat = null;
if ($injection->_id) {
  $injection->loadRefVaccinations();
  $repeat = $injection->_ref_vaccinations[0]->loadRefVaccine()->repeat_recall;
} else {
  $injection->injection_date = CMbDT::dateTime();
}

$vaccinated = ($injection->speciality != "N/A" || $injection->batch != "N/A");

$smarty = new CSmartyDP();

$smarty->assign("injection", $injection);
$smarty->assign("patient", $patient);
$smarty->assign("vaccinated", $vaccinated);
$smarty->assign("repeat", $repeat);

$smarty->display("inc_edit_vaccination");
