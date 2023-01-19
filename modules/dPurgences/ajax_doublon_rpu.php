<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$nb_month = CView::get("month_maintenance", "num default|1");

CView::checkin();
CView::enforceSlave();

$date = CMbDT::dateTime("-" . $nb_month . "MONTH");

$crequest = new CRequest();
$crequest->addSelect(array("`sejour`.`sejour_id`", "`sejour`.`sortie`"));
$crequest->addTable("`sejour`");
$crequest->addLJoinClause("rpu", "`sejour`.`sejour_id` = `rpu`.`sejour_id`");
$crequest->addWhereClause("sejour.entree", "> '$date'");
$crequest->addGroup(array("`sejour_id`"));
$crequest->addHaving(array("COUNT(`sejour`.`sejour_id`)>1"));

$sejour         = new CSejour();
$ds             = $sejour->getDS();
$list_id_sejour = $ds->loadList($crequest->makeSelect());

usort($list_id_sejour, function ($a, $b) {
  return -strnatcmp($a["sortie"], $b["sortie"]);
});

$list_sejour = array();
$patients    = array();
$guesses     = array();
foreach ($list_id_sejour as $_id_sejour) {
  $sejour = new CSejour();
  $sejour->load($_id_sejour["sejour_id"]);
  $sejour->loadBackRefs("rpu");
  $sejour->loadRefPatient();
  $sejour->loadNDA();

  if (!isset($patients[$sejour->patient_id])) {
    $patients["$sejour->patient_id"] = $sejour->_ref_patient;
  }

  $patients["$sejour->patient_id"]->_ref_sejours[$sejour->_id] = $sejour;
}

$mergeables_count = 0;
foreach ($patients as $patient_id => $patient) {
  $patient->loadIPP();

  $guess = array();
  $nicer = array();

  $guess["mergeable"] = isset($guesses[$patient_id]) ? true : false;

  // Sibling patients
  $siblings = $patient->getSiblings();
  foreach ($guess["siblings"] = array_keys($siblings) as $sibling_id) {
    if (array_key_exists($sibling_id, $patients)) {
      $guesses[$sibling_id]["mergeable"] = true;
      $guess["mergeable"]                = true;
    }
  }

  // Phoning patients
  $phonings = $patient->getPhoning($sejour->entree);
  foreach ($guess["phonings"] = array_keys($phonings) as $phoning_id) {
    if (array_key_exists($phoning_id, $patients)) {
      $guesses[$phoning_id]["mergeable"] = true;
      $guess["mergeable"]                = true;
    }
  }

  // Multiple séjours
  if (count($patient->_ref_sejours) > 1) {
    $guess["mergeable"] = true;
  }

  $where            = array();
  $where["annulee"] = " = '0'";
  // Multiple Interventions
  foreach ($patient->_ref_sejours as $_sejour) {
    $operations = $_sejour->loadRefsOperations($where);
    foreach ($operations as $_operation) {
      $_operation->loadView();
    }

    if (count($operations) > 1) {
      $guess["mergeable"] = true;
    }

    // Multiple RPU
    if (count($_sejour->_back["rpu"]) > 1) {
      $guess["mergeable"] = true;
    }
  }

  if ($guess["mergeable"]) {
    $mergeables_count++;
  }

  $guesses[$patient->_id] = $guess;
}

CMbArray::pluck($patients, "nom");

$smarty = new CSmartyDP("modules/dPadmissions");

$smarty->assign("mergeables_count", $mergeables_count);
$smarty->assign("see_mergeable", true);
$smarty->assign("see_yesterday", true);
$smarty->assign("see_cancelled", true);
$smarty->assign("date", CMbDT::date());
$smarty->assign("patients", $patients);
$smarty->assign("guesses", $guesses);
$smarty->assign("module", "dPurgences");

$smarty->display("inc_identito_vigilance");