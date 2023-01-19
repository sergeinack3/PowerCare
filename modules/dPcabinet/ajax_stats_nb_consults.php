<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
// Current user and current function
$mediuser = CMediusers::get();
$function = $mediuser->loadRefFunction();

// Filter
$filter = new CPlageconsult();
$filter->_function_id       = CValue::get("_function_id", $function->type == "cabinet" ? $function->_id : null );
$filter->_other_function_id = CValue::get("_other_function_id");
$filter->_date_min          = CValue::get("_date_min", CMbDT::date("last month"));
$filter->_date_max          = CValue::get("_date_max", CMbDT::date());
$filter->_user_id           = CValue::get("_user_id", null);
$ds = $filter->_spec->ds;

if ($filter->_function_id || $filter->_user_id) {
  // Consultations
  $query = "CREATE TEMPORARY TABLE consultation_prime AS 
    SELECT 
      consultation_id,
      chir_id AS praticien_id, 
      patient_id,
      plageconsult.date AS consult_date
    FROM consultation
    LEFT JOIN plageconsult ON plageconsult.plageconsult_id = consultation.plageconsult_id
    LEFT JOIN users_mediboard ON users_mediboard.user_id = plageconsult.chir_id
    LEFT JOIN users ON users.user_id = users_mediboard.user_id
    WHERE plageconsult.date BETWEEN '$filter->_date_min' AND '$filter->_date_max'
    AND annule = '0'
    AND patient_id IS NOT NULL ";
    
  if ($filter->_function_id) {
    $query .= "AND users_mediboard.function_id = '$filter->_function_id';";
  }
  else {
    $query .= "AND users_mediboard.user_id = '$filter->_user_id';";
  }
  
  $ds->exec($query);
  
  // Consultations counts
  $query = "SELECT praticien_id, COUNT(*)
    FROM consultation_prime
    GROUP BY praticien_id;";
  $consultations_counts = $ds->loadHashList($query);
  
  // Patients
  $query = "CREATE TEMPORARY TABLE consultation_patient
    SELECT praticien_id, patient_id
    FROM consultation_prime
    GROUP BY patient_id, praticien_id;";
  $ds->exec($query);
  
  // Patients counts
  $query = "SELECT praticien_id, COUNT(*)
    FROM consultation_patient
    GROUP BY praticien_id;";
  $patients_counts = $ds->loadHashList($query);

  // Sejours
  $query = "CREATE TEMPORARY TABLE consultation_sejour
    SELECT 
      consultation_prime.praticien_id, 
      consultation_prime.patient_id,
      consultation_id, 
      consult_date, 
      sejour.sejour_id, 
      sejour.entree, 
      sejour.sortie
    FROM consultation_prime
    LEFT JOIN sejour ON sejour.patient_id = consultation_prime.patient_id 
      AND sejour.praticien_id = consultation_prime.praticien_id 
      AND sejour.entree BETWEEN '$filter->_date_min' AND '$filter->_date_max'
      AND sejour.annule = '0'
    WHERE sejour.sejour_id IS NOT NULL
    GROUP BY consultation_id;";
  $ds->exec($query);
  
  // Sejours counts
  $query = "SELECT praticien_id, COUNT(*)
    FROM consultation_sejour
    GROUP BY praticien_id;";
  $sejours_counts = $ds->loadHashList($query);

  if ($filter->_other_function_id) {
    // Other (consultations)
    $query = "CREATE TEMPORARY TABLE consultation_other
      SELECT 
        consultation_prime.praticien_id, 
        consultation_prime.patient_id,
        consultation_prime.consultation_id AS consult1_id, 
        consult_date AS consult1_date, 
        consultation.consultation_id AS consult2_id, 
        plageconsult.date AS consult2_date
      FROM consultation_prime
      LEFT JOIN consultation ON consultation.patient_id = consultation_prime.patient_id 
      LEFT JOIN plageconsult ON plageconsult.plageconsult_id = consultation.plageconsult_id
      LEFT JOIN users_mediboard ON users_mediboard.user_id = plageconsult.chir_id
      WHERE users_mediboard.function_id = '$filter->_other_function_id'
      AND plageconsult.date BETWEEN '$filter->_date_min' AND '$filter->_date_max'
      GROUP BY consult1_id;";
    
    $ds->exec($query);
    
    // Other (consultations) counts
    $query = "SELECT praticien_id, COUNT(*)
      FROM consultation_other
      GROUP BY praticien_id;";
    $others_counts = $ds->loadHashList($query);
  }
  else {
    $others_counts = array();
  }
}

// Praticiens
$praticiens = $mediuser->loadProfessionnelDeSante(PERM_READ, $filter->_function_id);

// Stats by praticiens
$stats = array();
foreach ($praticiens as $prat_id => $_praticien) {
  if (!isset($consultations_counts[$_praticien->_id]) && !isset($sejours_counts[$_praticien->_id]) &&
      !isset($patients_counts     [$_praticien->_id]) && !isset($others_counts [$_praticien->_id])
  ) {
    unset($praticiens[$prat_id]);
    continue;
  }
  // Counts
  $counts = array (
    "consultations" => CMbArray::get($consultations_counts, $_praticien->_id),
    "sejours"       => CMbArray::get($sejours_counts      , $_praticien->_id),
    "patients"      => CMbArray::get($patients_counts     , $_praticien->_id),
    "others"        => CMbArray::get($others_counts       , $_praticien->_id),
  );
  
  // Percents
  $percents = array (
    "consultations" => $counts["consultations"] ? 1 : null,
    "sejours"       => $counts["consultations"] ? $counts["sejours"]  / $counts["consultations"] : null,
    "patients"      => $counts["consultations"] ? $counts["patients"] / $counts["consultations"] : null,
    "others"        => $counts["consultations"] ? $counts["others"]   / $counts["consultations"] : null,
  );
  
  $stats[$_praticien->_id] = array(
    "counts"   => $counts,
    "percents" => $percents,
  );
}

$smarty = new CSmartyDP();

$smarty->assign("filter"    , $filter);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("stats"     , $stats);

$smarty->display("inc_stats_nb_consults.tpl");
