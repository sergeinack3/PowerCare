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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Maternite\CNaissance;

CCanDo::checkRead();
$order_col          = CView::get("order_col", "enum list|patient_id|naissance|nom default|patient_id", true);
$order_way          = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$naissance_date_min = CMbDT::date("- 1 day") . " 08:00:00";
$naissance_date_max = CMbDT::date() . " 08:00:00";
CView::checkin();

$group         = CGroups::loadCurrent();
$naissance_ids = array();
$naissance     = new CNaissance();
$ds            = $naissance->getDS();

$ljoin = array(
  "sejour"   => "sejour.sejour_id = naissance.sejour_enfant_id",
  "patients" => "patients.patient_id = sejour.patient_id"
);

// J0: Jour de leur naissance entre 8 heures la veille à 8 le jour actuel.
$where                        = array();
$where["sejour.group_id"]     = " = '$group->_id'";
$where["naissance.date_time"] = "BETWEEN '$naissance_date_min' AND '$naissance_date_max'";

$naissances_j0 = $naissance->loadIds($where, "naissance.num_naissance, patients.nom", null, null, $ljoin);

// J3: Pour les bébés nés par voie basse.
$where                           = array();
$where["sejour.group_id"]        = " = '$group->_id'";
$where["naissance.by_caesarean"] = " = '0' ";
$where[]                         = "DATE_FORMAT(DATE_ADD(DATE(naissance.date_time), INTERVAL 3 DAY),'%d/%m/%Y') = DATE_FORMAT(DATE(NOW()),'%d/%m/%Y')";

$naissances_j3 = $naissance->loadIds($where, "naissance.num_naissance, patients.nom", null, null, $ljoin);

// J4: Pour les bébés nés par césarienne.
$where                           = array();
$where["sejour.group_id"]        = " = '$group->_id'";
$where["naissance.by_caesarean"] = " = '1' ";
$where[]                         = "DATE_FORMAT(DATE_ADD(DATE(naissance.date_time), INTERVAL 4 DAY),'%d/%m/%Y') = DATE_FORMAT(DATE(NOW()),'%d/%m/%Y')";

$naissances_j4 = $naissance->loadIds($where, "naissance.num_naissance, patients.nom", null, null, $ljoin);

$naissance_ids = array_merge($naissances_j0, $naissances_j3);
$naissance_ids = array_merge($naissance_ids, $naissances_j4);
$naissance_ids = array_unique($naissance_ids);

$order = null;
if ($order_col == "patient_id") {
    $order = "patients.nom $order_way, patients.prenom $order_way";
} elseif ($order_col == "naissance") {
    $order = "naissance.date_time $order_way";
}

$where = array(
  "naissance_id" => $ds->prepareIn($naissance_ids)
);

/** @var CNaissance[] $naissances */
$naissances = $naissance->loadList($where, $order, null, null, $ljoin);

$services_selected = array();
$sejours_np        = array();

CStoredObject::massLoadBackRefs($naissances, "exams_bebe");
$sejours = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");
$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
$lits         = CStoredObject::massLoadFwdRef($affectations, "lit_id");
CStoredObject::massLoadFwdRef($affectations, "service_id");
$chambres = CStoredObject::massLoadFwdRef($lits, "chambre_id");
$services = CStoredObject::massLoadFwdRef($chambres, "service_id");

$sejours_maman = CStoredObject::massLoadFwdRef($naissances, "sejour_maman_id");
CStoredObject::massLoadFwdRef($sejours_maman, "grossesse_id");

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
  $sejour->_ref_last_affectation->_ref_lit->loadCompleteView();

  $patient = $sejour->loadRefPatient();
  $patient->getFirstConstantes();

  // Passage dans un service Néonatalogie
  $_naissance->_service_neonatalogie = false;
  foreach ($affectations as $_affectation) {
    $service = $_affectation->loadRefService();
    if ($service->neonatalogie) {
      $_naissance->_service_neonatalogie = true;
    }
  }

  // Mère
  $sejour_mere = $_naissance->loadRefSejourMaman();
  $grossesse   = $sejour_mere->loadRefGrossesse();
  $grossesse->loadRefDossierPerinat();

  if (in_array($_naissance->_id, $naissances_j0)) {
    $_naissance->_consult_pediatre = "J0";
  }
  elseif (in_array($_naissance->_id, $naissances_j3)) {
    $_naissance->_consult_pediatre = "J3";
  }
  elseif (in_array($_naissance->_id, $naissances_j4)) {
    $_naissance->_consult_pediatre = "J4";
  }

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
$smarty->display("vw_print_pediatric_consult");

