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
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();
$sejour_date_min    = CView::get("_date_min", "dateTime default|now", true);
$sejour_date_max    = CView::get("_date_max", "dateTime default|now", true);
$naissance_date_min = CView::get("_datetime_min", "dateTime");
$naissance_date_max = CView::get("_datetime_max", "dateTime");
$pediatre_id        = CView::get("pediatre_id", "ref class|CMediusers", true);
$etat               = CView::get("state", "enum list|present|consult_pediatre|none default|present", true);
$order_col          = CView::get("order_col", "enum list|patient_id|naissance|nom default|patient_id", true);
$order_way          = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$services_ids       = CView::get("services_ids", "str", true);

$services_ids = CService::getServicesIdsPref($services_ids);
CView::checkin();

$group     = CGroups::loadCurrent();
$naissance = new CNaissance();
$where     = array();

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

if ($etat == 'present') {
    $where["sejour.sortie_reelle"] = "IS NULL";
}

$naissances = $naissance->loadList($where, $order, null, null, $ljoin);

$sejours = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
$affectations = CStoredObject::massLoadBackRefs($sejours, "affectations", "sortie DESC");
foreach ($naissances as $key => $_naissance) {
    $sejour = $_naissance->loadRefSejourEnfant();
    $sejour->loadRefsAffectations();
    if (
        !in_array(
            $sejour->_ref_last_affectation->service_id,
            $services_ids
        )
        && $sejour->_ref_last_affectation->service_id !== null
    ) {
        unset($naissances[$key]);
    }
    $sejour->_ref_last_affectation;
}

$services_selected = array();
$sejours_np        = array();

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

CStoredObject::massLoadFwdRef($sejours, "patient_id");
$lits         = CStoredObject::massLoadFwdRef($affectations, "lit_id");
$chambres     = CStoredObject::massLoadFwdRef($lits, "chambre_id");
$services     = CStoredObject::massLoadFwdRef($chambres, "service_id");

$sejours_maman = CStoredObject::massLoadFwdRef($naissances, "sejour_maman_id");
CStoredObject::massLoadFwdRef($sejours_maman, "grossesse_id");

CStoredObject::massLoadBackRefs($sejours, "prescriptions", null, array("type" => "= 'sejour'"));

foreach ($affectations as $_affectation) {
    $service = $_affectation->loadRefService();
}

/** @var CNaissance $_naissance */
foreach ($naissances as $_naissance) {
  $sejour = $_naissance->loadRefSejourEnfant();
  $sejour->loadRefsAffectations();
  $sejour->_ref_last_affectation->loadRefLit();
  $sejour->_ref_last_affectation->_ref_lit->loadCompleteView();

  $patient = $sejour->loadRefPatient();
  $patient->getFirstConstantes();

  // Mère
  $sejour_mere = $_naissance->loadRefSejourMaman();
  $grossesse   = $sejour_mere->loadRefGrossesse();
  $grossesse->loadRefDossierPerinat();

  // Planifications des éléments
  $where                     = array();
  CPrescription::$_load_lite = 1;
  $where                     = array(
    "type" => "= 'sejour'"
  );

  CPrescription::$_load_lite = 0;

  $elt       = new CElementPrescription();
  $rubriques = $elt->_specs["rubrique_feuille_trans"]->_list;

  // unset type_app
  unset($rubriques[0]);

  $prescriptions = array();
  $sejour->loadRefsPrescriptions();
  $_prescription                                           = $sejour->_ref_prescriptions["sejour"];
  $_prescription->_ref_prescription_lines_element_rubrique = array_fill_keys($rubriques, array());

  $sejour->_ref_prescription_sejour = $_prescription;

  if ($_prescription->_id) {
    $prescriptions[$_prescription->_id] = $_prescription;
  }

  /*$where_planif = array(
    "dateTime" => "BETWEEN '$today 00:00:00' AND '$today 23:59:59'"
  );*/

  foreach ($rubriques as $_rubrique) {
    $where = array(
      "rubrique_feuille_trans" => "= '$_rubrique'",
      "feuille_trans"          => "= '1'"
    );

    $elts_ids = $elt->loadIds($where);

    if (!count($elts_ids)) {
      continue;
    }

    $where = array(
      "prescription_id"         => CSQLDataSource::prepareIn(array_keys($prescriptions)),
      "element_prescription_id" => CSQLDataSource::prepareIn($elts_ids)
    );

    $lines_elt = CStoredObject::massLoadBackRefs($prescriptions, "prescription_line_element", null, $where, null, $_rubrique);

    // On ne conserve que les lignes ayant des planifications
    CStoredObject::massCountBackRefs($lines_elt, "planifications");

    /** @var CPrescription $_prescription */
    foreach ($prescriptions as $_prescription) {
      foreach ($_prescription->_back[$_rubrique] as $_line_elt) {
        if (!$_line_elt->_count["planifications"] || !$_line_elt->_current_active) {
          continue;
        }

        $_prescription->_ref_prescription_lines_element_rubrique[$_rubrique][$_line_elt->element_prescription_id] = $_line_elt;
      }
    }
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
$smarty->assign("services", $services);
$smarty->assign("services_selected", $services_selected);
$smarty->assign("order_col", $order_col);
$smarty->assign("order_way", $order_way);
$smarty->display("vw_print_transmission_sheet");

