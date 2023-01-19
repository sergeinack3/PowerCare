<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::getRefCheckRead("patient_id", "ref class|CPatient");
$praticien_id = CView::get("praticien_id", "ref class|CMediusers");

CView::checkin();

$patient = CPatient::findOrFail($patient_id);
$injections = $patient->loadRefInjections();
$praticien = CMediusers::get($praticien_id);
$etablissement = CGroups::loadCurrent();

CStoredObject::massLoadBackRefs($injections, "vaccinations");
$vaccinated = [];
foreach ($injections as $_injection) {
  $_injection->loadRefVaccinations();
  foreach ($_injection->_ref_vaccinations as $_vaccination) {
    $_vaccination->loadRefVaccine();
  }
  if ($_injection->isVaccinated()) {
    $vaccinated[] = $_injection->_id;
  }
}

$smarty = new CSmartyDP();
$smarty->assign('etablissement', $etablissement);
$smarty->assign('praticien', $praticien);
$smarty->assign('patient', $patient);
$smarty->assign("injections", $injections);
$smarty->assign("vaccinated", $vaccinated);
$smarty->display("vaccination/print_injection");
