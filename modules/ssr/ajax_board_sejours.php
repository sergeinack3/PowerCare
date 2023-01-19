<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\System\CPlanningWeek;

global $m;

CCanDo::checkEdit();
$mode          = CValue::get("mode", "count");
$date          = CValue::getOrSession("date", CMbDT::date());
$kine_id       = CValue::getOrSession("kine_id");
$hide_noevents = CValue::getOrSession("hide_noevents");

$mediuser = new CMediusers();
$mediuser->load($kine_id);

$planning = new CPlanningWeek($date);

$group_id = CGroups::get()->_id;

// Sejour SSR
$sejour = new CSejour();
$counts = array();

/** @var CSejour[] $sejours */
$sejours = array();

$ds = CSQLDataSource::get("std");

// Sejours pour lesquels le kine est référent
if ($mode == "count" || $mode == "referenced") {
  $join                        = array();
  $join["patients"]            = "patients.patient_id = sejour.patient_id";
  $order                       = "nom, prenom";
  $join["bilan_ssr"]           = "bilan_ssr.sejour_id = sejour.sejour_id";
  $join["technicien"]          = "technicien.technicien_id = bilan_ssr.technicien_id";
  $where                       = array();
  $where["sejour.type"]        = "= '$m'";
  $where["sejour.entree"]      = "<= '$planning->date_max'";
  $where["sejour.sortie"]      = ">= '$planning->date_min'";
  $where["sejour.annule"]      = "= '0'";
  $where["technicien.kine_id"] = "= '$kine_id'";
  $where["sejour.group_id"]    = "= '$group_id'";

  if ($mode == "count") {
    $counts["referenced"] = $sejour->countList($where, null, $join);
  }
  else {
    $sejours = $sejour->loadList($where, $order, null, null, $join);
  }
}

// Sejours pour lesquels le kine est remplaçant
if ($mode == "count" || $mode == "replaced") {
  $order                               = "nom, prenom";
  $join                                = array();
  $join["patients"]                    = "patients.patient_id = sejour.patient_id";
  $join["replacement"]                 = "replacement.sejour_id = sejour.sejour_id";
  $join["plageconge"]                  = "plageconge.plage_id = replacement.conge_id";
  $where                               = array();
  $where["sejour.type"]                = "= '$m'";
  $where["sejour.entree"]              = "<= '$planning->date_max'";
  $where["sejour.sortie"]              = ">= '$planning->date_min'";
  $where["sejour.annule"]              = "= '0'";
  $where["replacement.replacement_id"] = "IS NOT NULL";
  $where["replacement.replacer_id"]    = " = '$kine_id'";
  $where["plageconge.date_debut"]      = "<= '$planning->date_max'";
  $where["plageconge.date_fin"]        = ">= '$planning->date_min'";
  $where["sejour.group_id"]            = "= '$group_id'";

  if ($mode == "count") {
    $counts["replaced"] = $sejour->countList($where, null, $join);
  }
  else {
    $sejours = $sejour->loadList($where, $order, null, null, $join);
  }
}

// Sejours pour lesquels le rééducateur a des événements
if ($mode == "count" || $mode == "planned") {
  $order                        = "nom, prenom";
  $join                         = array();
  $join["patients"]             = "patients.patient_id = sejour.patient_id";
  $join["evenement_ssr"]        = "evenement_ssr.sejour_id = sejour.sejour_id";
  $where                        = array();
  $where["sejour.type"]         = "= '$m'";
  $where["sejour.annule"]       = "= '0'";
  $where["evenement_ssr.debut"] = " BETWEEN '" . CMbDT::dateTime($planning->date_min) . "' AND '" . CMbDT::dateTime($planning->date_max) . "'";
  $where[]                      = "evenement_ssr.therapeute_id = '$kine_id' OR evenement_ssr.therapeute2_id = '$kine_id' OR evenement_ssr.therapeute3_id = '$kine_id'";
  $where["sejour.group_id"]     = "= '$group_id'";
  $group                        = "sejour.sejour_id";

  if ($mode == "count") {
    // Do not use countList which won't work due to group by statement
    $counts["planned"] = count($sejour->loadIds($where, $order, null, $group, $join));
  }
  else {
    $sejours = $sejour->loadList($where, $order, null, $group, $join);
  }
}

// Sejours pour lesquels le rééducateur est exécutant pour des lignes prescrites mais n'a pas encore d'evenement planifiés
if ($mode == "count" || $mode == "plannable") {
  if (!CModule::getActive("dPprescription")) {
    $counts["plannable"] = null;
    $sejours             = array();
  }
  else {
    // Séjours élligibles
    $where                    = array();
    $where["sejour.type"]     = "= '$m'";
    $where["sejour.entree"]   = "<= '$planning->date_max'";
    $where["sejour.sortie"]   = ">= '$planning->date_min'";
    $where["sejour.annule"]   = "= '0'";
    $where["sejour.group_id"] = "= '$group_id'";

    $sejour_ids = $sejour->loadIds($where);

    // Identifiants de catégorie de prescriptions disponibles
    $function     = $mediuser->loadRefFunction();
    $executants   = $function->loadBackRefs("executants_prescription");
    $category_ids = CMbArray::pluck($executants, "category_prescription_id");

    // Recherche des lignes de prescriptions executables
    $line                                                   = new CPrescriptionLineElement;
    $join                                                   = array();
    $where                                                  = array();
    $join["element_prescription"]                           = "element_prescription.element_prescription_id = prescription_line_element.element_prescription_id";
    $where["element_prescription.category_prescription_id"] = $ds->prepareIn($category_ids);
    $join["prescription"]                                   = "prescription.prescription_id = prescription_line_element.prescription_id";
    $where["prescription.type"]                             = "= 'sejour'";
    $where["prescription.object_class"]                     = "= 'CSejour'";
    $where["prescription.object_id"]                        = $ds->prepareIn($sejour_ids);
    $line_ids                                               = $line->loadIds($where, null, null, null, $join);

    // Prescriptions exécutables
    $query = new CRequest;
    $query->addSelect("DISTINCT prescription_id");
    $query->addTable("prescription_line_element");
    $query->addWhereClause("prescription_line_element_id", $ds->prepareIn($line_ids));
    $prescription_ids = $ds->loadColumn($query->makeSelect());

    // Séjours planifiables
    $query = new CRequest;
    $query->addSelect("DISTINCT object_id");
    $query->addTable("prescription");
    $query->addWhereClause("prescription_id", $ds->prepareIn($prescription_ids));
    $sejour_ids = $ds->loadColumn($query->makeSelect());

    $where              = array();
    $where["sejour_id"] = $ds->prepareIn($sejour_ids);
    $join               = array();
    $join["patients"]   = "patients.patient_id = sejour.patient_id";
    $order              = "nom, prenom";

    if ($mode == "count") {
      $counts["plannable"] = $sejour->countList($where, null, $join);
    }
    else {
      $sejours = $sejour->loadList($where, $order, null, null, $join);
    }
  }
}

// Mode count
if ($mode == "count") {
  $smarty = new CSmartyDP("modules/ssr");
  $smarty->assign("counts", $counts);
  $smarty->assign("hide_noevents", $hide_noevents);
  $smarty->display("inc_board_sejours");

  return;
}

// Chargement des détails affichés de chaque séjour
$patients = CMbObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
foreach ($sejours as $_sejour) {
  $_sejour->countEvenementsSSRWeek($kine_id, $planning->date_min, $planning->date_max);
  if ($hide_noevents && !$_sejour->_count_evenements_ssr_week) {
    unset($sejours[$_sejour->_id]);
    continue;
  }
  $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
  $_sejour->loadRefBilanSSR()->getDatesEnCours($planning->date_min, $planning->date_max);

  // Modification des prescription
  if ($prescription = $_sejour->loadRefPrescriptionSejour()) {
    $prescription->loadRefsLinesElementByCat();
    if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
      $prescription->_count_alertes = $prescription->countAlertsNotHandled("medium");
    }
    else {
      $prescription->countFastRecentModif();
    }
  }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("sejours", $sejours);
$smarty->assign("mode", $mode);
$smarty->display("inc_board_list_sejours");
