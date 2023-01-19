<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$do_merge = CValue::get("do_merge");

$naissance = CValue::getOrSession(
  "naissance", array(
    "day"   => 1,
    "month" => 1,
    "year"  => 1,
  )
);

$ds = CSQLDataSource::get("std");

$query = "SELECT COUNT(*) AS total,
  CONVERT( GROUP_CONCAT(`patient_id` SEPARATOR '|') USING latin1 ) AS ids , 
  LOWER( CONCAT_WS( '-' 
    ,REPLACE( REPLACE( REPLACE( REPLACE( `nom` ,             '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' )
    ,REPLACE( REPLACE( REPLACE( REPLACE( `prenom` ,          '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' )
    ,REPLACE( REPLACE( REPLACE( REPLACE( `nom_jeune_fille` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' )
    ";

/*$query .= "
    ,QUOTE( REPLACE( REPLACE( REPLACE( REPLACE( `prenom_2` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) )
    ,QUOTE( REPLACE( REPLACE( REPLACE( REPLACE( `prenom_3` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) )
    ,QUOTE( REPLACE( REPLACE( REPLACE( REPLACE( `prenom_4` , '\\\\', '' ) , \"'\", '' ) , '-', '' ) , ' ', '' ) )";*/

if (!empty($naissance["year"])) {
  $query .= ", DATE_FORMAT(`naissance`, '%Y')";
}

if (!empty($naissance["month"])) {
  $query .= ", DATE_FORMAT(`naissance`, '%m')";
}

if (!empty($naissance["day"])) {
  $query .= ", DATE_FORMAT(`naissance`, '%d')";
}

$query .= " 
  )) AS `hash`
  FROM `patients`
  GROUP BY `hash`
  HAVING `total` > 1";

$res = $ds->query($query);

CAppUI::stepAjax(intval($ds->numRows($res)) . " patients identiques");

$patient_siblings = array();
if (!$do_merge) {
  $n = 100;
  while ($n-- && ($l = $ds->fetchAssoc($res))) {
    $patient_ids = explode("|", $l["ids"]);

    $patients = array();
    foreach ($patient_ids as $_id) {
      $_patient = new CPatient;
      $_patient->load($_id);
      $patients[] = $_patient;
    }

    $patient_siblings[] = array("siblings" => $patients, "hash" => $l["hash"]);
  }
}
/*
else {
  while($l = $ds->fetchAssoc($res)){
    $patient_ids = explode("|", $l["ids"]);
    
    $patients = array();
    foreach($patient_ids as $id) {
      $p = new CPatient;
      $p->load($id);
      $patients[$id] = $p;
    }
    
    $first_patient = array_shift($patients);
    $first_patient_id = $first_patient->_id;
    
    foreach($patients as $_patient) {
      $patients_array = array($_patient);
      if ($msg = $first_patient->mergePlainFields($patients_array)) {
        CAppUI::stepAjax("$_patient : $msg", UI_MSG_WARNING);
        continue;
      }
      
      // @todo mergePlainFields resets the _id 
      $first_patient->_id = $first_patient_id;
      
      $first_patient->_merging = $patients_array;
      if ($msg = $first_patient->merge($patients_array)) {
        CAppUI::stepAjax("$_patient : $msg", UI_MSG_WARNING);
      }
    }
    
    if (!$msg) CAppUI::stepAjax("Patient $first_patient fusionné");
  }
}*/

$smarty = new CSmartyDP();

$smarty->assign("patient_siblings", $patient_siblings);

$smarty->display("inc_merge_matching_patients.tpl");
