<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$consult_id = CView::get("consult_id", "ref class|CConsultAnesth");
$patient_id = CView::getRefCheckEdit("patient_id", "ref class|CPatient");

CView::checkin();

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($consult_id);
$consult_anesth->apfel_femme      = 0;
$consult_anesth->apfel_atcd_nvp   = 0;
$consult_anesth->apfel_morphine   = 0;
$consult_anesth->apfel_non_fumeur = 1;

$patient = new CPatient();
$patient->load($patient_id);
$dossier_medical = $patient->loadRefDossierMedical();

// Femme
if ($patient->sexe === "f") {
  $consult_anesth->apfel_femme = 1;
}

// Non fumeur
if (count($dossier_medical->_codes_cim)) {
  $is_fumeur = 0;
  
  foreach ($dossier_medical->_codes_cim as $_code_cim) {
    if (preg_match("/^(F17|T652|Z720|Z864|Z587)/", $_code_cim)) {
      $is_fumeur = 1;
      break;
    }
  }
  
  if ($is_fumeur) {
    $consult_anesth->apfel_non_fumeur = 0;
  }
}

$dossier_medical->loadRefsAntecedents(false, false, false, true);
if (count($dossier_medical->_all_antecedents)) {
  $consult_anesth->apfel_atcd_nvp = 1;
}

$smarty = new CSmartyDP();
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->display("inc_guess_score_apfel.tpl");
