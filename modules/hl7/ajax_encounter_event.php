<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$patient_id = CValue::getOrSession("patient_id");
$event      = CValue::get("event");
$event_type = CValue::get("event_type");

$patient = new CPatient();
$patient->load($patient_id);
$patient->loadIPP();

$smarty = new CSmartyDP();
$smarty->assign("event_type", $event_type);
$smarty->assign("event"     , $event);
$smarty->assign("patient"   , $patient);

switch ($event) {
  case "A02":
    $where = array(
      "patient_id"     => "= '$patient->_id'",
      "entree_reelle"  => "IS NOT NULL",
    );
    $sejour  = new CSejour();
    $patient->_ref_sejours = $sejour->loadList($where, "entree DESC");
    CSejour::massLoadNDA($patient->_ref_sejours);
    break;
  case "A03":
    $sejours = $patient->loadRefsSejours(array("sortie_reelle" => "IS NULL"));
    CSejour::massLoadNDA($sejours);
    break;
  case "A11":
    $where = array();
    if ($event_type == "register_outpatient") {
      $where["type"] = "= 'urg'";
    }
    $sejours = $patient->loadRefsSejours($where);
    CSejour::massLoadNDA($sejours);
    break;
  case "A12":
    $leftjoin = array(
      "affectation" => "affectation.sejour_id = sejour.sejour_id"
    );
    $where = array(
      "patient_id"     => "= '$patient->_id'",
      "entree_reelle"  => "IS NOT NULL",
      "affectation_id" => "IS NOT NULL"
    );
    $sejour  = new CSejour();
    /** @var CSejour[] $sejours */
    $sejours = $sejour->loadList($where, "entree DESC", null, null, $leftjoin);
    $patient->_ref_sejours = $sejours;
    CSejour::massLoadNDA($sejours);
    CSejour::massLoadBackRefs($sejours, "affectations");
    foreach ($sejours as $_sejour) {
      $_sejour->_ref_affectations = $_sejour->_back["affectations"];
    }
    break;
  case "A13":
    $sejours = $patient->loadRefsSejours(array("sortie_reelle" => "IS NOT NULL"));
    CSejour::massLoadNDA($sejours);
    break;
  case "A38":
    $sejours = $patient->loadRefsSejours(array("sortie_reelle" => "IS NULL", "entree_reelle" => "IS NULL"));
    CSejour::massLoadNDA($sejours);
    break;
  case "INSERT":
    $where = array("entree_reelle" => "IS NOT NULL");
    if ($event_type == "event_change_class_inpatient") {
      $where["type"] = "= 'urg' OR type = 'ambu'";
    }
    if ($event_type == "event_change_class_outpatient") {
      $where["type"] = "= 'comp'";
    }
    $sejours = $patient->loadRefsSejours($where);
    CSejour::massLoadNDA($sejours);
    break;
  default:
}

$smarty->display("test_hl7/inc_encounter_event_$event.tpl");

