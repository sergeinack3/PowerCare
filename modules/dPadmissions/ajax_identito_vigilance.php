<?php
/**
 * @package Mediboard\Admissions
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
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

CCanDo::checkEdit();

// Filtres
$see_mergeable = CView::get("see_mergeable", "bool default|1", true);
$see_yesterday = CView::get("see_yesterday", "bool default|1", true);
$see_cancelled = CView::get("see_cancelled", "bool default|1", true);
$module        = CView::get("module", "str default|dPadmissions", true);
$date          = CView::get("date", "date default|now");

CView::checkin();
CView::enforceSlave();

// Selection de la date
$date_min = $see_yesterday ? CMbDT::date("-1 day", $date) : $date;
$date_max = CMbDT::date("+1 day", $date);

// Chargement des séjours concernés
$sejour = new CSejour();
$where = array(
  "sejour.entree"   => "BETWEEN '$date_min' AND '$date_max'",
  "sejour.group_id" => "= '".CGroups::loadCurrent()->_id."'"
);

if ($module == "dPurgences") {
  $where["sejour.type"] = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
}
if ($see_cancelled == 0) {
  $where["sejour.annule"] = "= '0'";
}

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, "entree");

CSejour::massLoadNDA($sejours);
CStoredObject::massLoadFwdRef($sejours, "patient_id");
CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", array("annulee" => "= '0'"));

$guesses = array();
/** @var CPatient[] $patients */
$patients = array();
$_sejour  = new CSejour();
foreach ($sejours as $_sejour) {
  if ($module == "dPurgences") {
    // Look for multiple RPU
    // Simulate loading as for now loading RPU are outrageously resource consuming
    // @todo use loadBackRef() as soon as CRPU.updateFormFields() get sanitized
    $_sejour->_back["rpu"] = array();
    foreach ($_sejour->loadBackIds("rpu") as $_rpu_id) {
      $rpu = new CRPU();
      $rpu->_id = $_rpu_id;
      $_sejour->_back["rpu"][$rpu->_id] = $rpu;

    }
  }

  // Chargement de l'IPP
  $_sejour->loadRefPatient();
  
  // Classement par patient
  if (!isset($patients[$_sejour->patient_id])) {
    //Cas des patients anonymes où un loadrefSejour est fait
    $_sejour->_ref_patient->_ref_sejours = array();
    $patients[$_sejour->patient_id] = $_sejour->_ref_patient;
  }

  $patients[$_sejour->patient_id]->_ref_sejours[$_sejour->_id] = $_sejour;
}

// Chargement des détails sur les patients
$mergeables_count = 0;

CPatient::massLoadIPP($patients);

foreach ($patients as $patient_id => $patient) {
  $guess = array();
  $nicer = array();

  $guess["mergeable"] = isset($guesses[$patient_id]) ? true : false;
  
  // Sibling patients
  $siblings = $patient->getSiblings();
  foreach ($guess["siblings"] = array_keys($siblings) as $sibling_id) {
    if (array_key_exists($sibling_id, $patients)) {
      $guesses[$sibling_id]["mergeable"] = true;
      $guess["mergeable"] = true;
    }
  }

  // Phoning patients
  $phonings = $patient->getPhoning($date);
  foreach ($guess["phonings"] = array_keys($phonings) as $phoning_id) {
    if (array_key_exists($phoning_id, $patients)) {
      $guesses[$phoning_id]["mergeable"] = true;
      $guess["mergeable"] = true;
    }
  }
  
  // Multiple séjours 
  if (count($patient->_ref_sejours) > 1) {
    $guess["mergeable"] = true;
  }

  // Multiple Interventions
  foreach ($patient->_ref_sejours as $_sejour) {
    $operations = $_sejour->loadRefsOperations();
    foreach ($operations as $_operation) {
      $_operation->loadRefPlageOp();
    }
    
    if (count($operations) > 1) {
      $guess["mergeable"] = true;
    }
    
    // Multiple RPU 
    if ($module == "dPurgences") {
      if (count($_sejour->_back["rpu"]) > 1) {
        $guess["mergeable"] = true;
      }
    }
  }  
  
  if ($guess["mergeable"]) {
    $mergeables_count++;
  }

  $guesses[$patient->_id] = $guess;
}

// Tri sur la vue a posteriori : détruit les clés !
$patient_ordered = CMbArray::pluck($patients, "nom");
array_multisort($patient_ordered, SORT_ASC, $patients);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("mergeables_count", $mergeables_count);
$smarty->assign("see_mergeable"   , $see_mergeable);
$smarty->assign("see_yesterday"   , $see_yesterday);
$smarty->assign("see_cancelled"   , $see_cancelled);
$smarty->assign("date"            , $date);
$smarty->assign("patients"        , $patients);
$smarty->assign("guesses"         , $guesses);
$smarty->assign("module"          , $module);
$smarty->assign("allow_merge"     , CSejour::getAllowMerge());

$smarty->display("inc_identito_vigilance.tpl");
