<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Maternite\CNaissance;

CCanDo::checkRead();
$sejour_date_min    = CView::get("_date_min", "dateTime default|now", true);
$sejour_date_max    = CView::get("_date_max", "dateTime default|now", true);
$naissance_date_min = CView::get("_datetime_min", "dateTime");
$naissance_date_max = CView::get("_datetime_max", "dateTime");
$pediatre_id        = CView::get("pediatre_id", "ref class|CMediusers", true);
$services_ids       = CView::get("services_ids", "str", true);
$order_col          = CView::get("order_col", "enum list|patient_id|naissance|nom default|patient_id", true);
$order_way          = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
CView::checkin();

$group = CGroups::loadCurrent();
$where = array();

if (!$sejour_date_min && !$sejour_date_max) {
  $sejour_date_min = CMbDT::dateTime();
  $sejour_date_max = CMbDT::dateTime();
}

if (($sejour_date_min && $sejour_date_max) && (!$naissance_date_min && !$naissance_date_max)) {
  $where = array(
    "sejour.entree" => "<= '$sejour_date_max'",
    "sejour.sortie" => ">= '$sejour_date_min'",
  );
}

if ($naissance_date_min && $naissance_date_max) {
  $where["naissance.date_time"] = "BETWEEN '$naissance_date_min' AND '$naissance_date_max'";
}

if ($pediatre_id) {
  $where[] = "sejour.praticien_id = '$pediatre_id'";
}

$ljoin = array(
  "sejour"   => "sejour.sejour_id = naissance.sejour_enfant_id",
  "patients" => "patients.patient_id = sejour.patient_id"
);

$where["sejour.group_id"] = " = '$group->_id'";

$order = null;
if ($order_col == "patient_id") {
    $order = "patients.nom $order_way, patients.prenom $order_way";
} elseif ($order_col == "naissance") {
    $order = "naissance.date_time $order_way";
}

$naissance  = new CNaissance();
$naissances = $naissance->loadList($where, $order, null, null, $ljoin);

$sejours = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
foreach ($naissances as $key => $_naissance) {
    $sejour = $_naissance->loadRefSejourEnfant();
    $sejour->loadRefsAffectations();
    if ($services_ids &&
        !in_array(
            $sejour->_ref_last_affectation->service_id,
            $services_ids
        )
        && $sejour->_ref_last_affectation->service_id !== null
    ) {
        unset($naissances[$key]);
        continue;
    }
    $sejour->_ref_last_affectation->loadRefLit();
}

$services_selected = array();
$sejours_np        = array();

CStoredObject::massLoadBackRefs($naissances, "exams_bebe");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
$lits         = CStoredObject::massLoadFwdRef($affectations, "lit_id");
$chambres     = CStoredObject::massLoadFwdRef($lits, "chambre_id");
$services     = CStoredObject::massLoadFwdRef($chambres, "service_id");

if ($order_col == "nom") {
    foreach ($naissances as $_naissance) {
        $sejour = $_naissance->loadRefSejourEnfant();
        $sejour->loadRefsAffectations();
        $sejour->_ref_last_affectation->loadRefLit();
        $sejour->_ref_last_affectation->_ref_lit->loadCompleteView();
    }

    $lits       = CMbArray::pluck($naissances, "_ref_sejour_enfant", "_ref_last_affectation", "_ref_lit");
    $sorter_lit = CMbArray::pluck($lits, "_view");
    
    array_multisort(
        $sorter_lit,
        constant("SORT_$order_way"),
        $naissances
    );
}

/** @var CNaissance $_naissance */
foreach ($naissances as $_naissance) {
  $_naissance->loadRefsExamenNouveauNe();
  $last_examen = $_naissance->loadRefLastExamenNouveauNe();
  $last_examen->getOEAExam();
  $last_examen->checkGuthrieExam();
  $last_examen->loadRefGuthrieUser();

  $sejour = $_naissance->loadRefSejourEnfant();
  $sejour->loadRefsAffectations();
  $sejour->_ref_last_affectation->loadRefLit();
  $sejour->_ref_last_affectation->loadRefService();
  $sejour->_ref_last_affectation->_ref_lit->loadCompleteView();

  $patient = $sejour->loadRefPatient();
  $patient->getFirstConstantes();

  if (!$sejour->_ref_last_affectation->service_id) {
    $sejours_np[$sejour->_id] = $_naissance;
  }
  elseif ($sejour->_ref_last_affectation->service_id) {
    $services_selected[$sejour->_ref_last_affectation->_ref_service->nom][$_naissance->_id] = $_naissance;
  }

  if (count($sejours_np)) {
    $services_selected["NP"] = $sejours_np;
  }
}

ksort($services_selected);
//Non placés en fin de liste
if (array_key_exists("NP", $services_selected)) {
    $np = $services_selected['NP'];
    unset($services_selected['NP']);
    $services_selected['NP'] = $np;
}

// Récupération de la liste des services
$where              = array();
$where["externe"]   = "= '0'";
$where["cancelled"] = "= '0'";

$service  = new CService();
$services = $service->loadGroupList($where);

$smarty = new CSmartyDP();
$smarty->assign("services"         , $services);
$smarty->assign("services_selected", $services_selected);
$smarty->display("vw_print_pediatric_nurse");

