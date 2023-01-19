<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Soins\CRDVExterne;

CCanDo::checkRead();
$service_id = CView::get("service_id", "str default|none", true);
$date       = CView::get("date", "date default|now", true);
$date_min   = CView::get("date_min", "date default|now", true);
$date_max   = CView::get("date_max", "date default|now", true);
$print      = CView::get("print", "bool default|0", true);
CView::checkin();

$services = explode(",", $service_id);
CMbArray::removeValue("", $services);
$rdv_by_services = array();

$ljoin                = array();
$ljoin["sejour"]      = "sejour.sejour_id = rdv_externe.sejour_id";
$ljoin["patients"]    = "patients.patient_id = sejour.patient_id";
$ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id";

$where                           = array();
$where["rdv_externe.date_debut"] = "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'";
$where["affectation.service_id"] = CSQLDataSource::prepareIn($services);

$order = "patients.nom ASC, rdv_externe.date_debut DESC";

$rdv_externe  = new CRDVExterne();
$rdv_externes = $rdv_externe->loadList($where, $order, null, null, $ljoin);

$sejours = CStoredObject::massLoadFwdRef($rdv_externes, "sejour_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");

foreach ($rdv_externes as $_rdv_externe) {
  $sejour  = $_rdv_externe->loadRefSejour();
  $patient = $sejour->loadRefPatient();
  $sejour->loadRefsAffectations();
  $sejour->_ref_last_affectation->loadRefLit()->loadRefChambre();
  $sejour->_ref_last_affectation->loadRefService();

  $rdv_by_services[$sejour->service_id]["patients"][$patient->_id]["patient"]                  = $patient;
  $rdv_by_services[$sejour->service_id]["patients"][$patient->_id]["rdvs"][$_rdv_externe->_id] = $_rdv_externe;
  $rdv_by_services[$sejour->service_id]["affectation"]                                         = $sejour->_ref_last_affectation;
}

$smarty = new CSmartyDP();
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);

if (!$print) {
  $smarty->display("vw_filter_all_rdv_externes");
}
else {
  $smarty->assign("rdv_by_services", $rdv_by_services);
  $smarty->display("vw_print_all_rdv_externes");
}
