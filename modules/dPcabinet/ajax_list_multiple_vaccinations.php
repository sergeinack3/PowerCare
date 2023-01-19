<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Cabinet\Vaccination\CVaccinRepository;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$injection_id = CView::get("vaccination_id", "ref class|CInjection");
$patient_id   = CView::getRefCheckEdit("patient_id", "ref class|CPatient");
$types        = (array)CView::get("types", ["prop" => "str", "default" => []]);
$recall_age   = CView::get("recall_age", "str");
$repeat       = CView::get("repeat", "num");

CView::checkin();

$i_object = new CInjection();
$ljoin    = ["vaccination" => "injection.injection_id = vaccination.injection_id"];
$where    = ["patient_id"       => "= '$patient_id'",
             "vaccination.type" => CSQLDataSource::prepareIn($types)];
if ($recall_age) {
  $where["recall_age"] = "= '$recall_age'";
}

$injections = $i_object->loadList($where, null, null, null, $ljoin);
CStoredObject::massLoadBackRefs($injections, "vaccinations");
foreach ($injections as $_injection) {
  $_injection->loadRefVaccinations();
}

$vaccs_rep = new CVaccinRepository();

$injection             = CInjection::findOrNew($injection_id);
$injection->recall_age = $recall_age;
$injection->loadRefVaccinations();
if ($injection->_ref_vaccinations) {
  foreach ($injection->_ref_vaccinations as $_vaccination) {
    $_vaccination->loadRefVaccine();
  }
}
else {
  $vaccinations = array();
  foreach ($types as $_type) {
    $vaccination               = new CVaccination();
    $vaccination->type         = $_type;
    $vaccination->_ref_vaccine = $vaccs_rep->findByType($_type);
    $vaccinations[]            = $vaccination;
  }
  $injection->_ref_vaccinations = $vaccinations;
}

$patient     = CPatient::findOrFail($patient_id);
$user        = CMediusers::get();
$user_can_do = $patient->canDo();

$vaccine = reset($injection->_ref_vaccinations)->_ref_vaccine;
$repeat  = (isset($vaccine->recall)) ? end($vaccine->recall)->repeat : 0;

$smarty = new CSmartyDP();

$smarty->assign("types", $types);
$smarty->assign("vaccines", $vaccs_rep->getAll());
$smarty->assign("user_can_do", $user_can_do);
$smarty->assign("patient", $patient);
$smarty->assign("repeat", $repeat);
$smarty->assign("label_read_only", "1");
$smarty->assign("injections", $injections);
$smarty->assign("empty_injection", $injection);
$smarty->assign("recall_age", null);

$smarty->display("vaccination/inc_list_injections");
