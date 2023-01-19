<?php
/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSoundex2;
use Ox\Core\CValue;
use Ox\Interop\Hprim21\CHprim21Patient;

/**
 * Vue de la liste des patients HPRIM
 */
CCanDo::checkRead();

$showCount = 30;

// Chargement du patient sélectionné
$patient_id = CValue::getOrSession("patient_id");
$patient = new CHprim21Patient();
$patient->load($patient_id);

// Récuperation des patients recherchés
$patient_nom         = CValue::getOrSession("nom"         , ""       );
$patient_prenom      = CValue::getOrSession("prenom"      , ""       );
$patient_jeuneFille  = CValue::getOrSession("jeuneFille"  , ""       );
$patient_ville       = CValue::getOrSession("ville"       , ""       );
$patient_cp          = CValue::getOrSession("cp"          , ""       );
$patient_day         = CValue::get("Date_Day"    , "");
$patient_month       = CValue::get("Date_Month"  , "");
$patient_year        = CValue::get("Date_Year"   , "");
$patient_naissance   = null;

$where        = array();
$whereSoundex = array();
$soundexObj   = new CSoundex2();

if ($patient_nom) {
  $patient_nom = trim($patient_nom);
  $where["nom"]                 = "LIKE '$patient_nom%'";
  $whereSoundex["nom_soundex2"] = "LIKE '".$soundexObj->build($patient_nom)."%'";
}
if ($patient_prenom) {
  $patient_prenom = trim($patient_prenom);
  $where["prenom"]                 = "LIKE '$patient_prenom%'";
  $whereSoundex["prenom_soundex2"] = "LIKE '".$soundexObj->build($patient_prenom)."%'";
}
if ($patient_jeuneFille) {
  $patient_jeuneFille = trim($patient_jeuneFille);
  $where["nom_jeune_fille"]        = "LIKE '$patient_jeuneFille%'";
  $whereSoundex["nomjf_soundex2"]  = "LIKE '".$soundexObj->build($patient_jeuneFille)."%'";
}

if (($patient_year) || ($patient_month) || ($patient_day)) {
  $patient_naissance = "on";
}

if ($patient_naissance == "on") {
  $year =($patient_year)?"$patient_year-":"%-";
  $month =($patient_month)?"$patient_month-":"%-";
  $day =($patient_day)?"$patient_day":"%";
  if ($day!="%") {
    $day = str_pad($day, 2, "0", STR_PAD_LEFT);
  }
  
  $naissance = $year.$month.$day;
  
  if ($patient_year || $patient_month || $patient_day) {
    $where["naissance"] = $whereSoundex["naissance"] = "LIKE '$naissance'";
  }
}

if ($patient_ville) {
  $where["ville"] = $whereSoundex["ville"] = "LIKE '$patient_ville%'";
}
if ($patient_cp) {
  $where["cp"] = $whereSoundex["cp"] = "= '$patient_cp'";
}

$patients        = array();
$patientsSoundex = array();

$order = "nom, prenom, naissance";
$pat = new CHprim21Patient();

// Patient counts
$patientsCount = $where ? $pat->countList($where) : 0;
$patientsSoundexCount = $whereSoundex ? $pat->countList($whereSoundex) : 0;
$patientsSoundexCount -= $patientsCount;

// Chargement des patients
if ($where) {
  $patients = $pat->loadList($where, $order, "0, $showCount");
}

if ($whereSoundex) {
  $patientsSoundex = $pat->loadList($whereSoundex, $order, "0, $showCount");
  $patientsSoundex = array_diff_key($patientsSoundex, $patients);
}

// Sélection du premier de la liste si aucun n'est déjà sélectionné
if (!$patient->_id and count($patients) == 1) {
  $patient = reset($patients);
}

if ($patient->_id) {
  $patient->loadRefs();
  foreach ($patient->_ref_hprim21_sejours as &$sejour) {
    $sejour->loadRefs();
    $sejour->_ref_sejour->loadRefsFwd();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("nom"                 , $patient_nom              );
$smarty->assign("prenom"              , $patient_prenom           );
$smarty->assign("jeuneFille"          , $patient_jeuneFille       );
$smarty->assign("naissance"           , $patient_naissance        );
$smarty->assign("ville"               , $patient_ville            );
$smarty->assign("cp"                  , $patient_cp               );

$smarty->assign("patients"            , $patients                 );
$smarty->assign("patientsSoundex"     , $patientsSoundex          );
$smarty->assign("patientsCount"       , $patientsCount            );
$smarty->assign("patientsSoundexCount", $patientsSoundexCount     );

$smarty->assign("patient"             , $patient                  );

$smarty->display("vw_patients.tpl");

