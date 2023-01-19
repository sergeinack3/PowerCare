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
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;

CCanDo::checkRead();

$service_id   = CView::get("service_id", "ref class|CService");
$services_ids = CView::get("services_ids", "str", true);
$services_ids = CService::getServicesIdsPref($services_ids);
CView::checkin();

// Chargement des séjours à afficher en fonction de l'id
if ($service_id) {
  $services_ids              = array();
  $services_ids[$service_id] = "$service_id";
}

CAppUI::requireModuleFile("hospi", "inc_vw_affectations");

$today = CMbDT::date();

// Chargement des affectations
$where_service               = array();
$where_service["service_id"] = CSQLDataSource::prepareIn($services_ids);

$service  = new CService();
$services = $service->loadList($where_service);

$nb_columns = array();
$counter    = 0;

foreach ($services as $_service) {
  loadServiceComplet($_service, $today, 0);

  $sejours  = CMbArray::pluck($_service->_ref_affectations, "_ref_sejour");
  $sejours  = array_combine(CMbArray::pluck($sejours, "_id"), $sejours);
  $patients = CMbArray::pluck($sejours, "_ref_patient");
  $patients = array_combine(CMbArray::pluck($patients, "_id"), $patients);

  CStoredObject::massLoadFwdRef($sejours, "confirme_user_id");
  CStoredObject::massLoadFwdRef($sejours, "etablissement_sortie_id");
  CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");

  $feuiile_trans_sejour_estimated = CAppUI::gconf("soins dossier_soins feuille_trans_sejour_estimated");

  $dossiers_medicaux = array();

  $type_view_demande_particuliere = CAppUI::pref("type_view_demande_particuliere");
  $degre                          = $type_view_demande_particuliere == "last_macro" ? null : "low";
  if (in_array($type_view_demande_particuliere, array("trans_hight", "macro_hight"))) {
    $degre = "high";
  }
  $cible_importante = in_array($type_view_demande_particuliere, array("last_macro", "macro_low", "macro_hight")) ? true : false;
  $important        = $cible_importante ? false : true;

  CAffectation::massUpdateView($_service->_ref_affectations);

  /** @var CAffectation $_affectation */
  foreach ($_service->_ref_affectations as $_affectation) {
    $sejour         = $_affectation->_ref_sejour;
    $affectation_id = $_affectation->_id;
    $lit_id         = $_affectation->lit_id;
    $chambre_id     = $_affectation->_ref_lit->chambre_id;

    if ($sejour->sortie_reelle || !$sejour->_id) {
      unset($_service->_ref_affectations[$affectation_id]);
      unset($_service->_ref_chambres[$chambre_id]->_ref_lits[$lit_id]->_ref_affectations[$affectation_id]);
      continue;
    }

    if (!$feuiile_trans_sejour_estimated && !$sejour->entree_reelle) {
      unset($_service->_ref_affectations[$affectation_id]);
      unset($_service->_ref_chambres[$chambre_id]->_ref_lits[$lit_id]->_ref_affectations[$affectation_id]);
      continue;
    }

    $patient = $sejour->_ref_patient;
    $sejour->loadJourOp($today);
    $sejour->loadRefConfirmeUser();
    $sejour->loadRefConfirmeUser();
    $sejour->loadRefEtablissementTransfert();
    $sejour->loadRefsTransmissions(true, false, false, 1, null);
    $dossier_medical                          = $patient->loadRefDossierMedical(false);
    $dossiers_medicaux[$dossier_medical->_id] = $dossier_medical;
  }

  // Antécédents et allergies
  $detail_atcd_alle = CAppUI::pref("detail_atcd_alle");

  if ($detail_atcd_alle) {
    $where = array(
      "type"   => "= 'alle'",
      "rques"  => CSQLDataSource::prepareNotIn(explode("|", addslashes(CAppUI::gconf("soins Other ignore_allergies")))),
      'annule' => "= '0'"
    );
    CStoredObject::massLoadBackRefs($dossiers_medicaux, "antecedents", null, $where, null, "allergies");

    unset($where["type"]);
    $where[] = "type != 'alle' OR type IS NULL";
    CStoredObject::massLoadBackRefs($dossiers_medicaux, "antecedents", null, $where);

    foreach ($dossiers_medicaux as $_dossier_medical) {
      $_dossier_medical->_ref_allergies = $_dossier_medical->_back["allergies"];

      foreach ($_dossier_medical->_back["antecedents"] as $_atcd) {
        $_dossier_medical->_ref_antecedents_by_type_appareil[$_atcd->type][$_atcd->appareil][] = $_atcd;
      }
    }
  }
  else {
    $counts_allergie   = CDossierMedical::massCountAllergies(array_keys($dossiers_medicaux));
    $counts_antecedent = CDossierMedical::massCountAntecedents(array_keys($dossiers_medicaux), false);

    foreach ($dossiers_medicaux as $_dossier_medical) {
      $_dossier_medical->_count_allergies   =
        array_key_exists($_dossier_medical->_id, $counts_allergie) ? $counts_allergie[$_dossier_medical->_id] : 0;
      $_dossier_medical->_count_antecedents =
        array_key_exists($_dossier_medical->_id, $counts_antecedent) ? $counts_antecedent[$_dossier_medical->_id] : 0;
    }
  }

// Planifications des éléments
  CPrescription::$_load_lite = 1;
  $where                     = array(
    "type" => "= 'sejour'"
  );
  CStoredObject::massLoadBackRefs($sejours, "prescriptions", null, $where);
  CPrescription::$_load_lite = 0;

  $elt       = new CElementPrescription();
  $rubriques = $elt->_specs["rubrique_feuille_trans"]->_list;

  $prescriptions = array();
  /** @var CSejour $_sejour */
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefsPrescriptions();

    $_prescription                                           = $_sejour->_ref_prescriptions["sejour"];
    $_prescription->_ref_prescription_lines_element_rubrique = array_fill_keys($rubriques, array());

    $_sejour->_ref_prescription_sejour = $_prescription;

    if ($_prescription->_id) {
      $prescriptions[$_prescription->_id] = $_prescription;
    }
  }

  $where_planif = array(
    "dateTime" => "BETWEEN '$today 00:00:00' AND '$today 23:59:59'"
  );

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
    if (CPrescription::isPlanSoinsActive()) {
      CStoredObject::massCountBackRefs($lines_elt, "planifications", $where_planif);
    }

    /** @var CPrescription $_prescription */
    foreach ($prescriptions as $_prescription) {
      foreach ($_prescription->_back[$_rubrique] as $_line_elt) {
        if (!$_line_elt->sans_planif
          && ((CPrescription::isPlanSoinsActive() && !$_line_elt->_count["planifications"]) || !$_line_elt->_current_active)) {
          continue;
        }

        $_prescription->_ref_prescription_lines_element_rubrique[$_rubrique][$_line_elt->element_prescription_id] = $_line_elt;
        $nb_columns[$_rubrique]                                                                                   = $_line_elt;
      }
    }
  }

  $counter = 0;
}

$smarty = new CSmartyDP();
$smarty->assign("services", $services);
$smarty->assign("nb_columns", $nb_columns);
$smarty->display("vw_feuille_transmissions");
