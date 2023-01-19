<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\System\CAlert;

CCanDo::checkRead();

$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$date_min     = CView::get("date_min", "date default|now", true);
$date_max     = CView::get("date_max", "date default|now", true);
$service_id   = CView::get("service_id", "ref class|CService", true);
$services_ids = CView::get("services_ids", "str", true);
$praticien_id = CView::get("praticien_id", "ref class|CMediusers", true);

CView::checkin();

$sejour  = new CSejour();
$sejours = array();

if (CAppUI::gconf("soins Sejour select_services_ids")) {
  $services_ids = CService::getServicesIdsPref($services_ids);
  if ($services_ids) {
    $service_id = null;
  }
}
else {
  $services_ids = array();
}

if ($sejour->load($sejour_id)) {
  $sejours[$sejour->_id] = $sejour;

  CAccessMedicalData::logAccess($sejour);
}
else {
  // Première recherche sur les alertes présentes dans l'intervalle demandé
  $alert = new CAlert();

  $where = array(
    "alert.tag"                      => "= 'prescription_modification'",
    "alert.object_class"             => "= 'CPrescriptionLineElement'",
    "alert.handled"                  => "= '0'",
    "category_prescription.chapitre" => "= 'kine'"
  );

  $ljoin = array(
    "prescription_line_element" => "prescription_line_element.prescription_line_element_id = alert.object_id",
    "element_prescription"      => "element_prescription.element_prescription_id = prescription_line_element.element_prescription_id",
    "category_prescription"     => "category_prescription.category_prescription_id = element_prescription.category_prescription_id"
  );

  // Identifiants des prescriptions
  $prescriptions_ids = $alert->loadColumn("prescription_id", $where, $ljoin);

  // Identifiants des séjours
  $prescription = new CPrescription();
  $sejours_ids  = $prescription->loadColumn("object_id", array("prescription_id" => CSQLDataSource::prepareIn($prescriptions_ids)));

  // Filtres demandés pour restreindre la liste précédente et chargement des séjours
  $where = array(
    "sejour.sejour_id" => CSQLDataSource::prepareIn($sejours_ids),
  );

  $ljoin = array();

  if ($praticien_id) {
    $where["sejour.praticien_id"] = "= '$praticien_id'";
  }

  if ($service_id || count($services_ids)) {
    $ljoin = array(
      "affectation" => "affectation.sejour_id = sejour.sejour_id"
    );

    $where["affectation.service_id"] = CSQLDataSource::prepareIn($services_ids, $service_id);

    $where["affectation.entree"] = "<= '$date_max 23:59:59'";
    $where["affectation.sortie"] = ">= '$date_min 00:00:00'";
  }

  $sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);
}

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CPatient::massCountPhotoIdentite($patients);
$consults = CStoredObject::massLoadBackRefs(
  $sejours, "consultations", "date DESC, heure DESC", null,
  array("plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id")
);
CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC");
$plages = CStoredObject::massLoadFwdRef($consults, "plageconsult_id");
CSejour::massLoadRefPrescriptionSejour($sejours);
CStoredObject::massLoadFwdRef($plages, "chir_id");

// Chargement des alertes et rattachement à chaque prescription
CPrescriptionLineElement::$_load_extra_lite = true;
foreach ($sejours as $_sejour) {
  $_prescription = $_sejour->_ref_prescription_sejour;
  $_prescription->loadRefsLinesElement(null, "kine");

  $_prescription->_ref_alertes =
    CStoredObject::massLoadBackRefs(
      $_prescription->_ref_prescription_lines_element, "alerts", null,
      array("tag" => "= 'prescription_modification'", "handled" => "= '0'")
    );
}
CPrescriptionLineElement::$_load_extra_lite = false;

foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient()->loadRefPhotoIdentite();
  $_sejour->loadRefPraticien();
  $_sejour->loadRefsConsultations();
  $_sejour->_ref_prescription_sejour->loadJourOp($date_min);

  foreach ($_sejour->_ref_consultations as $_consult) {
    $_consult->loadRefPlageConsult();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign($sejour->_id ? "_sejour" : "sejours", $sejour->_id ? $sejour : $sejours);

$smarty->display($sejour->_id ? "inc_line_sejour_reeducation" : "inc_list_sejours_reeducation");