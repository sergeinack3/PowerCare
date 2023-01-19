<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;

global $m;

$sejour_id  = CView::get("sejour_id", "ref class|CSejour", true);
$element_id = CView::get("_element_id", "ref class|CElementPrescription", true);
$order_way  = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col  = CView::get("order_col", "str default|patient_id|lit_id", true);

$spec_days      = array(
  "str",
  "default" => array()
);
$_days          = CView::get("_days", $spec_days);
$_sejours_guids = CView::get("_sejours_guids", "str");
$_sejours_guids = json_decode(utf8_encode(stripslashes($_sejours_guids)), true);
$date           = CView::get("date", "date default|now", true);

CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$monday = CMbDT::date("last monday", CMbDT::date("+1 day", $date));

$days = array();
foreach ($_days as $_number) {
  $days[] = CMbDT::date("+$_number DAYS", $monday);
}
if (!count($_days)) {
  $date = !count($_days) ? $date : reset($days);
}

$ljoin = array(
  "prescription"              => "sejour.sejour_id = prescription.object_id AND prescription.object_class = 'CSejour'",
  "prescription_line_element" => "prescription_line_element.prescription_id = prescription.prescription_id"
);

$group_id = CGroups::loadCurrent()->_id;
$where    = array(
  "prescription.prescription_id" => "IS NOT NULL",
  "element_prescription_id"      => " = '$element_id'",
  "sejour.type"                  => "= '$m'",
  "sejour.sejour_id"             => " <> '$sejour_id'",
  "sejour.annule"                => " = '0'",
  "sejour.group_id"              => " = '$group_id'"
);

/** @var CSejour[] $sejours */
$sejours = CSejour::loadListForDate($date, $where, "entree", null, "sejour_id", $ljoin);

/** @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

CStoredObject::massLoadFwdRef($sejours, "praticien_id");

CStoredObject::massLoadBackRefs($sejours, "bilan_ssr");
CStoredObject::massLoadBackRefs($sejours, "notes");

CSejour::massLoadCurrAffectation($sejours);

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

foreach ($sejours as $_sejour) {
  $patient = $_sejour->loadRefPatient();
  $_sejour->loadRefPraticien();
  $bilan = $_sejour->loadRefBilanSSR();
  $bilan->loadRefPraticienDemandeur();
  $bilan->loadRefKineReferent();
  $bilan->loadRefKineJournee();

  // Détail du séjour
  $_sejour->checkDaysRelative($date);
  $_sejour->loadRefsNotes();
  // Chargement du lit
  $_sejour->_ref_curr_affectation->loadRefLit();
}

$element = new CElementPrescription();
$element->load($element_id);

$colors = CColorLibelleSejour::loadAllFor(CMbArray::pluck($sejours, "libelle"));

if ($order_col == "lit_id") {
  $sorter = CMbArray::pluck($sejours, "_ref_curr_affectation", "_ref_lit", "_view");
  $order_entree =  CMbArray::pluck($sejours, "entree");
  array_multisort($sorter, constant("SORT_$order_way"), $order_entree, SORT_ASC, $sejours);
}
else {
    $order_nom    = CMbArray::pluck($sejours, "_ref_patient", "nom");
    $order_prenom = CMbArray::pluck($sejours, "_ref_patient", "prenom");
    $order_entree = CMbArray::pluck($sejours, "entree");
  array_multisort(
      $order_nom, constant("SORT_$order_way"),
      $order_prenom, constant("SORT_$order_way"),
      $order_entree, SORT_ASC,
      $sejours
  );
}

$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("sejours", $sejours);
$smarty->assign("date", $date);
$smarty->assign("_sejours_guids", $_sejours_guids);
$smarty->assign("element", $element);
$smarty->assign("colors", $colors);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);

$smarty->display("vw_patients_seance_collective");
