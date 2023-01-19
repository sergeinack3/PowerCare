<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$service_id         = CView::get("service_id", "ref class|CService");
$date               = CView::get("date", "date");
$user_id            = CView::get("user_id", "ref class|CMediusers");
$degre              = CView::get("degre", "str");
$load_transmissions = CView::get("transmissions", "bool");
$load_observations  = CView::get("observations", "bool");
$refresh            = CView::get("refresh", "str");
$real_time          = CView::get('real_time', "bool default|0", true);
$order_col          = CView::get("order_col", "enum list|patient_id|lit_id|date default|date");
$order_way          = CView::get("order_way", "enum list|ASC|DESC default|DESC");

CView::checkin();
CView::enforceSlave();

if ($date == CMbDT::date()) {
  $date_max = CMbDT::dateTime();
}
else {
  $date_max = CMbDT::date("+ 1 DAY", $date) . " 00:00:00";
}

$nb_hours = CAppUI::gconf("planSoins Pancarte transmissions_hours");
$date_min = CMbDT::dateTime(" - $nb_hours HOURS", $date_max);

// Chargement du service
$service = new CService();
$service->load($service_id);
$transmissions                   = array();
$observations                    = array();
$users                           = array();
$ljoin                           = array();
$where                           = array();
$where["affectation.service_id"] = " = '$service_id'";

$where["cancellation_date"] = "IS NULL";

if ($real_time) {
  $time                        = CMbDT::time();
  $where["affectation.entree"] = " <= '$date $time'";
  $where["affectation.sortie"] = " >= '$date $time'";
}
else {
  $where[] = "date BETWEEN '$date_min' AND '$date_max'";
}
if ($user_id) {
  $where["user_id"] = " = '$user_id'";
}
if ($degre) {
  if ($degre == "urg_normal") {
    $where["degre"] = "IN('low', 'high')";
  }
  if ($degre == "urg") {
    $where["degre"] = "= 'high'";
  }
}

// Chargement des transmissions
if ($load_transmissions == "1") {
  $ljoin["affectation"] = "transmission_medicale.sejour_id = affectation.sejour_id";
  $where["date"]        = "BETWEEN affectation.entree AND affectation.sortie";

  $transmission  = new CTransmissionMedicale();
  $transmissions = $transmission->loadList($where, null, null, "transmission_medicale_id", $ljoin, "date");

  CStoredObject::massLoadFwdRef($transmissions, "object_id");
  CStoredObject::massLoadFwdRef($transmissions, "cible_id");
  CStoredObject::massLoadFwdRef($transmissions, "user_id");
}

// Chargement des observations
if ($load_observations == "1") {
  $ljoin["affectation"] = "observation_medicale.sejour_id = affectation.sejour_id";

  $observation  = new CObservationMedicale();
  $observations = $observation->loadList($where, null, null, "observation_medicale_id", $ljoin);

  CStoredObject::massLoadFwdRef($observations, "user_id");
}

// Mass load communs
if (count($transmissions) || count($observations)) {
  $sejours_ids = array_merge(CMbArray::pluck($transmissions, "sejour_id"), CMbArray::pluck($observations, "sejour_id"));

  $sejour  = new CSejour();
  $sejours = $sejour->loadList(array("sejour_id" => CSQLDataSource::prepareIn($sejours_ids)));

  CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
}

$cibles        = array();
$trans_and_obs = array();
foreach ($transmissions as $_transmission) {
  $_transmission->loadTargetObject();
  $_transmission->loadRefSejour();
  $_transmission->loadRefUser();
  $_transmission->loadRefCible();
  $_transmission->_ref_sejour->loadRefPatient();
  $_transmission->_ref_sejour->loadRefsAffectations();
  $_transmission->_ref_sejour->_ref_last_affectation->loadRefLit();

  $patient = $_transmission->_ref_sejour->_ref_patient;
  $lit     = $_transmission->_ref_sejour->_ref_last_affectation->_ref_lit;

  if ($order_col == "patient_id") {
    $key = $patient->nom . $patient->prenom . $patient->_id . $_transmission->date;
  }
  if ($order_col == "date") {
    $key = $_transmission->date;
  }
  if ($order_col == "lit_id") {
    $key = $lit->_view . $lit->_id . $_transmission->date;
  }
  $_transmission->calculCibles($cibles);
  $trans_and_obs[$key][$_transmission->_id] = $_transmission;
  $users[$_transmission->user_id]           = $_transmission->_ref_user;
}

foreach ($observations as $_observation) {
  $_observation->loadRefSejour();
  $_observation->loadRefUser();
  $_observation->_ref_sejour->loadRefPatient();
  $_observation->_ref_sejour->loadRefsAffectations();
  $_observation->_ref_sejour->_ref_last_affectation->loadRefLit();

  $patient = $_observation->_ref_sejour->_ref_patient;
  $lit     = $_observation->_ref_sejour->_ref_last_affectation->_ref_lit;

  if ($order_col == "patient_id") {
    $key = $patient->nom . $patient->prenom . $patient->_id . $_observation->date;
  }
  if ($order_col == "date") {
    $key = $_observation->date;
  }
  if ($order_col == "lit_id") {
    $key = $lit->_view . $lit->_id . $_observation->date;
  }
  $trans_and_obs[$key][$_observation->_id] = $_observation;
  $users[$_observation->user_id]           = $_observation->_ref_user;
}

// Tri du tableau
if ($order_way == "ASC") {
  ksort($trans_and_obs);
}
else {
  krsort($trans_and_obs);
}

$filter_obs          = new CObservationMedicale();
$filter_obs->degre   = $degre;
$filter_obs->user_id = $user_id;

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->assign("cibles", $cibles);
$smarty->assign("service", $service);
$smarty->assign("transmissions", $transmissions);
$smarty->assign("observations", $observations);
$smarty->assign("trans_and_obs", $trans_and_obs);
$smarty->assign("filter_obs", $filter_obs);
$smarty->assign("users", $users);
$smarty->assign("with_filter", "1");
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign('real_time', $real_time);

if ($user_id || $degre || $refresh) {
  $smarty->display('../../dPprescription/templates/inc_vw_transmissions');
}
else {
  $smarty->display('inc_vw_transmissions_pancarte');
}
