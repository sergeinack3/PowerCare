<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$fast_object_guid = CView::get("fast_object_guid", "str default|");
$target = CView::get("target", "str default|init");
$facture_selected_guid = CView::get("facture_selected_guid", "str default|");
if (!$fast_object_guid) {
  $patient_id = CView::get("patient_id", "ref class|CPatient", true);
  $praticien_id = CView::get("praticien_id", "str default|-1", true);
  $use_disabled_praticien = CView::get("use_disabled_praticien", "bool default|", true);
  $date_max = CView::get("_date_max", "date default|now", true);
  $date_min = CView::get("_date_min", "date default|" . CMbDT::date("-1 month"), true);
  $selected_guid = CView::get("selected_guid", "str default|");
}

CView::checkin();
$fast_facture_object_guid = null;
$nb_element = 50;

$group_id = CGroups::loadCurrent()->_id;

// Données à charger dans le cadre d'un accès rapide depuis un objet
if ($fast_object_guid) {
  /** @var $object CConsultation|CSejour|CEvenementPatient */
  $object = CMbObject::loadFromGuid($fast_object_guid);

  if ($object instanceof CSejour || $object instanceof CConsultation) {
    CAccessMedicalData::logAccess($object);
  }

  $object->loadRefPatient();
  $object->loadRefPraticien();

  $object->loadRefFacture();
  if ($object instanceof CConsultation) {
    $object->loadRefPlageConsult();
    $date_min = CMbDT::date("-1 day", $object->_date);
    $date_max = CMbDT::date("+1 day", $object->_date);
  }
  else {
    $date_min = CMbDT::date("-1 day", $object->date);
    $date_max = CMbDT::date("+1 day", $object->date);
  }
  $object_facture = $object->_ref_facture;
  $praticien_id = $object->_ref_praticien->_id;
  if ($object_facture->_id && !$object_facture->cloture && !$object_facture->definitive) {
    if (CMbDT::date($object_facture->ouverture) < $date_min) {
      $date_min = CMbDT::date("-1 day", $object_facture->ouverture);
    }
    else {
      $date_max = CMbDT::date("+1 day", $object_facture->ouverture);
    }
    $praticien_id = $object_facture->praticien_id;
    $fast_facture_object_guid = $object_facture->_guid;
  }

  $patient_id = $object->_ref_patient->_id;
}

$smarty = new CSmartyDP();
$template = "factureliaison_manager" . ($target === "factureliaison_lists" ? "_lists" : "");
$consultations = array();
$factures = array();
$evts = array();
// Chargement des données de base (accès standard)
if ($target === "init") {
  $patient = new CPatient();
  $patient->load($patient_id);
  $consultation = new CConsultation();
  $consultation->_date_min = $date_min;
  $consultation->_date_max = $date_max;
  $praticien = new CMediusers();
  $praticiens = $praticien->loadPraticiens(PERM_EDIT);
  $all_praticiens = $praticien->loadPraticiens(PERM_EDIT, null, null, null, false);
  if ($praticien_id) {
    $praticien->load($praticien_id);
  }

  $smarty->assign("patient", $patient);
  $smarty->assign("consultation", $consultation);
  $smarty->assign("praticiens", $praticiens);
  $smarty->assign("all_praticiens", $all_praticiens);
  $smarty->assign("use_disabled_praticien", $use_disabled_praticien);
  $smarty->assign("praticien_id", $praticien_id);
  $smarty->assign("facture", new CFactureCabinet());
}

// Chargement des objets
if ($target === "objects_list" || $target === "factureliaison_lists" || $fast_object_guid) {
  $consultations = new CConsultation();
  $where_consultation = array(
    "functions_mediboard.group_id" => "= '$group_id'",
    "valide"                       => "= '0'",
    "plageconsult.date"            => "BETWEEN '$date_min' AND '$date_max'",
    "(facture_cabinet.facture_id IS NULL) OR (facture_cabinet.facture_id IS NOT NULL AND facture_cabinet.definitive <> '0' AND facture_cabinet.annule <> '0')",
    "(facture_etablissement.facture_id IS NULL) OR (facture_etablissement.facture_id IS NOT NULL AND facture_etablissement.definitive <> '0' AND facture_etablissement.annule <> '0')"
  );
  $where_evt = array(
    "valide"                       => "= '0'",
    "functions_mediboard.group_id" => "= '$group_id'",
    "evenement_patient.date"       => "BETWEEN '$date_min' AND '$date_max'",
  );
  $ljoin_consultation = array(
    "plageconsult"          => "plageconsult.plageconsult_id = consultation.plageconsult_id",
    "facture_liaison"       => "facture_liaison.object_id = consultation.consultation_id " .
      "AND facture_liaison.object_class= '" . $consultations->_class . "'",
    "facture_etablissement" => "facture_etablissement.facture_id = facture_liaison.facture_id " .
      "AND facture_liaison.facture_class = 'CFactureEtablissement'",
    "facture_cabinet"       => "facture_cabinet.facture_id = facture_liaison.facture_id " .
      "AND facture_liaison.facture_class = 'CFactureCabinet'",
    "users_mediboard"       => "users_mediboard.user_id = plageconsult.chir_id",
    "functions_mediboard"   => "functions_mediboard.function_id = users_mediboard.function_id"
  );
  $ljoin_evt = array(
    "users_mediboard"     => "users_mediboard.user_id = evenement_patient.praticien_id",
    "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id",
  );
  if ($patient_id) {
    $where_consultation["consultation.patient_id"] = "= '$patient_id'";
    $where_evt["dossier_medical.object_id"] = "= '$patient_id'";
    $where_evt["dossier_medical.object_class"] = "= 'CPatient'";
    $ljoin_evt["dossier_medical"] = "evenement_patient.dossier_medical_id = dossier_medical.dossier_medical_id";
  }
  if ($praticien_id) {
    $where_consultation["plageconsult.chir_id"] = "= '$praticien_id'";
    $where_evt["praticien_id"] = "= '$praticien_id'";
  }

  $count_obj = $consultations->countList($where_consultation, null, $ljoin_consultation);
  $consultations = $consultations->loadList($where_consultation, null, "0, $nb_element", "consultation_id", $ljoin_consultation);
  CStoredObject::massLoadFwdRef($consultations, "patient_id");
  foreach ($consultations as $_consultation) {
    $_consultation->loadRefPatient();
    $_consultation->loadRefPraticien();
  }

  if ($count_obj < $nb_element) {
    $evt = new CEvenementPatient();
    $count_obj += $evt->countList($where_evt, null, $ljoin_evt);
    $evts = $evt->loadList($where_evt, null, "0, " . ($nb_element - $count_obj), "evenement_patient_id", $ljoin_evt);
    CStoredObject::massLoadFwdRef($evts, "praticien_id");
    foreach ($evts as $_evt) {
      $_evt->loadRefPatient();
      $_evt->loadRefPraticien();
    }
  }

  $template = $target === "objects_list" ? "factureliaison_manager_objects_list" : $template;
  $smarty->assign("object_count_alert", $count_obj > $nb_element);
  $smarty->assign("object_nb_element", $nb_element);
}

// Chargement des factures
if ($target === "factures_list" || $target === "factureliaison_lists" || $fast_object_guid) {
  $facture_cab = new CFactureCabinet();
  $where = array(
    "group_id"   => "= '$group_id'",
    "cloture"    => "IS NULL",
    "definitive" => "= '0'",
    "extourne"   => "= '0'",
    "ouverture"  => "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'"
  );
  if ($patient_id) {
    $where["patient_id"] = "= '$patient_id'";
  }
  if ($praticien_id) {
    $where["praticien_id"] = "= '$praticien_id'";
  }
  $count_fc = $facture_cab->countList($where);
  $factures = $facture_cab->loadList($where, null, "0, $nb_element");
  if ($count_fc < $nb_element) {
    $facture_etab = new CFactureEtablissement();
    $count_etab = $facture_etab->countList($where);
    $factures = array_merge($factures, $facture_etab->loadList($where, null, "0, " . ($nb_element - $count_fc)));
    $count_fc += $count_etab;
  }
  CStoredObject::massLoadFwdRef($factures, "praticien_id");
  foreach ($factures as $_facture) {
    $_facture->loadRefPraticien();
    $_facture->loadRefsObjects();
    $_facture->loadRefPatient();
    CStoredObject::massLoadFwdRef($_facture->_ref_consults, "patient_id");
    foreach ($_facture->_ref_consults as $_consultation) {
      $_consultation->loadRefPatient();
    }
    if ($_facture instanceof CFactureCabinet) {
      CStoredObject::massLoadFwdRef($_facture->_ref_evts, "praticien_id");
      foreach ($_facture->_ref_evts as $_evt) {
        $_evt->loadRefPraticien();
        $_evt->loadRefPatient();
      }
    }
  }
  $template = $target === "factures_list" ? "factureliaison_manager_factures_list" : $template;
  $smarty->assign("facture_count_alert", $count_fc > $nb_element);
  $smarty->assign("facture_nb_element", $nb_element);
}

// Assignation des données communes
$smarty->assign("selected_guid", $fast_object_guid ?: $selected_guid);
$smarty->assign("facture_selected_guid", $fast_facture_object_guid ?: $facture_selected_guid);
$smarty->assign("consultations", $consultations);
$smarty->assign("evts", $evts);
$smarty->assign("factures", $factures);

$smarty->display("factureliaison_manager/$template");
