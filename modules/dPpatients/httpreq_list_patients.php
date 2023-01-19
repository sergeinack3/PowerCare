<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/// @todo: Ce fichier ressemble beaucoup à vw_idx_patient.php, il faudrait factoriser

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSoundex2;
use Ox\Core\CValue;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$board = CValue::get("board", 0);

$mediuser = CMediusers::get();

$patient_id = CValue::getOrSession("patient_id");

// Récuperation des patients recherchés
$patient_nom       = trim(CValue::getOrSession("nom"));
$patient_prenom    = trim(CValue::getOrSession("prenom"));
$patient_ville     = CValue::get("ville");
$patient_cp        = CValue::get("cp");
$patient_day       = CValue::getOrSession("Date_Day");
$patient_month     = CValue::getOrSession("Date_Month");
$patient_year      = CValue::getOrSession("Date_Year");
$patient_sexe      = CValue::get("sexe");
$patient_naissance = null;
$patient_ipp       = CValue::get("patient_ipp");
$patient_nda       = CValue::get("patient_nda");
$useVitale         = CValue::get("useVitale", CModule::getActive("fse") || CAppUI::pref('LogicielLectureVitale') != 'none' ? 1 : 0);
$prat_id           = CValue::get("prat_id");

$patVitale = new CPatient();
$patient   = new CPatient();

$patient_nom_search    = "";
$patient_prenom_search = "";

if ($new = CValue::get("new")) {
  $patient->load(null);
  CValue::setSession("patient_id", null);
  CValue::setSession("selClass", null);
  CValue::setSession("selKey", null);
}
else {
  $patient->load($patient_id);
}

// Champs vitale
if ($useVitale && CModule::getActive("fse")) {
  $cv = CFseFactory::createCV();
  if ($cv) {
    $cv->getPropertiesFromVitale($patVitale);
    $patVitale->updateFormFields();
    $patient_nom    = $patVitale->nom;
    $patient_prenom = $patVitale->prenom;
    CValue::setSession("nom", $patVitale->nom);
    CValue::setSession("prenom", $patVitale->prenom);
    $cv->loadFromIdVitale($patVitale);
  }
}

// Recherhche par IPP
if ($patient_ipp && !$useVitale) {
  // Initialisation dans le cas d'une recherche par IPP
  $patients             = array();
  $patientsSoundex      = array();
  $patientsCount        = 0;
  $patientsSoundexCount = 0;

  $patient       = new CPatient;
  $patient->_IPP = $patient_ipp;
  // Aucune configuration de IPP
  if (!$patient->getTagIPP()) {
    $patient->load($patient_ipp);
  }
  else {
    $patient->loadFromIPP();
  }
  if ($patient->_id) {
    CValue::setSession("patient_id", $patient->_id);
    $patients[$patient->_id] = $patient;
  }
}
// Recherche par trait standard
else {
  $where        = array();
  $whereSoundex = array();
  $ljoin        = array();
  $soundexObj   = new CSoundex2();
  // Limitation du nombre de caractères
  $patient_nom_search    = trim($patient_nom);
  $patient_prenom_search = trim($patient_prenom);
  if ($limit_char_search = CAppUI::gconf("dPpatients CPatient limit_char_search")) {
    $patient_nom_search    = substr($patient_nom_search, 0, $limit_char_search);
    $patient_prenom_search = substr($patient_prenom_search, 0, $limit_char_search);
  }

  if ($patient_nom_search) {
    $patient_nom_soundex = $soundexObj->build($patient_nom_search);
    $where[]             = "`nom` LIKE '$patient_nom_search%' OR `nom_jeune_fille` LIKE '$patient_nom_search%'";
    $whereSoundex[]      = "`nom_soundex2` LIKE '$patient_nom_soundex%' OR `nomjf_soundex2` LIKE '$patient_nom_soundex%'";
  }

  if ($patient_prenom_search) {
    $patient_prenom_soundex          = $soundexObj->build($patient_prenom_search);
    $where["prenom"]                 = "LIKE '$patient_prenom_search%'";
    $whereSoundex["prenom_soundex2"] = "LIKE '$patient_prenom_soundex%'";
  }

  if ($patient_year || $patient_month || $patient_day) {
    $patient_naissance  =
      CValue::first($patient_year, "%") . "-" .
      CValue::first($patient_month, "%") . "-" .
      CValue::first($patient_day, "%");
    $where["naissance"] = $whereSoundex["naissance"] = "LIKE '$patient_naissance'";
  }

  if ($patient_sexe) {
    $where["sexe"] = $whereSoundex["sexe"] = "= '$patient_sexe'";
  }

  if ($patient_ville) {
    $where["ville"] = $whereSoundex["ville"] = "LIKE '$patient_ville%'";
  }
  if ($patient_cp) {
    $where["cp"] = $whereSoundex["cp"] = "LIKE '$patient_cp%'";
  }

  if ($prat_id) {
    $ljoin["consultation"] = "`consultation`.`patient_id` = `patients`.`patient_id`";
    $ljoin["plageconsult"] = "`plageconsult`.`plageconsult_id` = `consultation`.`plageconsult_id`";
    $ljoin["sejour"]       = "`sejour`.`patient_id` = `patients`.`patient_id`";

    $where[]        = "plageconsult.chir_id = '$prat_id' OR sejour.praticien_id = '$prat_id'";
    $whereSoundex[] = "plageconsult.chir_id = '$prat_id' OR sejour.praticien_id = '$prat_id'";
  }

  if ($patient_nda) {
    $ljoin["sejour"]      = "`sejour`.`patient_id` = `patients`.`patient_id`";
    $ljoin["id_sante400"] = "`id_sante400`.`object_id` = `sejour`.`sejour_id`";

    $where[]                    = "`id_sante400`.`object_class` = 'CSejour'";
    $where["id_sante400.id400"] = " = '$patient_nda'";
  }

  $patients        = array();
  $patientsSoundex = array();

  $pat = new CPatient();
  if ($where) {
    $patients = $pat->loadList($where, "nom, prenom, naissance", "0, 100", null, $ljoin);
  }
  if ($whereSoundex && ($nbExact = (100 - count($patients)))) {
    $patientsSoundex = $pat->loadList($whereSoundex, "nom, prenom, naissance", "0, $nbExact", null, $ljoin);
    $patientsSoundex = array_diff_key($patientsSoundex, $patients);
  }
}

// Liste des praticiens
$prats = $mediuser->loadPraticiens();


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("dPsanteInstalled", CModule::getInstalled("dPsante400"));
$smarty->assign("patient_ipp", $patient_ipp);
$smarty->assign("patient_nda", $patient_nda);
$smarty->assign("board", $board);

$smarty->assign("nom", $patient_nom);
$smarty->assign("prenom", $patient_prenom);
$smarty->assign("ville", $patient_ville);
$smarty->assign("cp", $patient_cp);
$smarty->assign("naissance", $patient_naissance);
$smarty->assign("nom_search", $patient_nom_search);
$smarty->assign("prenom_search", $patient_prenom_search);
$smarty->assign("sexe", $patient_sexe);
$smarty->assign("prat_id", $prat_id);
$smarty->assign("prats", $prats);

$smarty->assign("useVitale", $useVitale);
$smarty->assign("patVitale", $patVitale);
$smarty->assign("patients", $patients);
$smarty->assign("patientsCount", count($patients));
$smarty->assign("patientsSoundexCount", count($patientsSoundex));
$smarty->assign("patientsSoundex", $patientsSoundex);
$smarty->assign("patient", $patient);

$smarty->display("inc_list_patient.tpl");
