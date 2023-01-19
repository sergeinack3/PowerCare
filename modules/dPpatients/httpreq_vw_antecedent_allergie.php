<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$sejour_id  = CView::get("sejour_id", "ref class|CSejour", true);
$patient_id = CView::get("patient_id", "ref class|CPatient", true);
$consult_id = CView::get("consult_id", "num");
CView::checkin();

if ($consult_id) {
  $consult = new CConsultation();
  $consult->load($consult_id);

  CAccessMedicalData::logAccess($consult);

  $patient = $consult->loadRefPatient();
  $sejour  = $consult->loadRefSejour();

  $sejour->loadRefDossierMedical();
  $dossier_medical_sejour =& $sejour->_ref_dossier_medical;
}
elseif ($sejour_id) {
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $sejour->loadRefPatient();
  $patient = $sejour->_ref_patient;

  $sejour->loadRefDossierMedical();
  $dossier_medical_sejour =& $sejour->_ref_dossier_medical;
}
else {
  $patient = new CPatient();
  $patient->load($patient_id);

  $dossier_medical_sejour = new CDossierMedical();
  $sejour                 = new CSejour();
}

if ($dossier_medical_sejour->_id) {
  $atcd_absence = $dossier_medical_sejour->loadRefsAntecedents(false, false, false, false, 1);

  $dossier_medical_sejour->loadRefsAntecedents();
  $dossier_medical_sejour->loadRefsAllergies();

  $dossier_medical_sejour->countAntecedents(false, true);
  $dossier_medical_sejour->countAllergies();

  CStoredObject::massLoadBackRefs($dossier_medical_sejour->_all_antecedents, "hypertext_links");
  foreach ($dossier_medical_sejour->_all_antecedents as $_atcd) {
    $_atcd->loadRefsHyperTextLink();
  }
}

$patient->loadRefDossierMedical();
$dossier_medical = $patient->_ref_dossier_medical;
if ($dossier_medical->_id) {
  $atcd_absence = $dossier_medical->loadRefsAntecedents(false, false, false, false, 1);
  $dossier_medical->loadRefsAntecedents();
  $dossier_medical->loadRefsAllergies();

  $dossier_medical->countAntecedents(false, true);
  $dossier_medical->countAllergies();

  CStoredObject::massLoadBackRefs($dossier_medical->_all_antecedents, "hypertext_links");
  foreach ($dossier_medical->_all_antecedents as $_atcd) {
    $_atcd->loadRefsHyperTextLink();
  }
}

if ($dossier_medical_sejour->_id && $dossier_medical->_id) {
  CDossierMedical::cleanAntecedentsSignificatifs($dossier_medical_sejour, $dossier_medical);
}

$where                      = array();
$where["sejour.annule"]     = " = '0'";
$where["sejour.patient_id"] = " = '$patient->_id'";
$where["sejour.group_id"]   = CSQLDataSource::prepareIn(array_keys(CGroups::loadGroups(PERM_READ)));
$_sejour                    = new CSejour();
/* @var CSejour[] $sejours */
$sejours = $_sejour->loadList($where, 'entree DESC');
foreach ($sejours as $_sejour) {
  $_sejour->loadRefsOperations();
  if (!$_sejour->_motif_complet) {
    unset($sejours[$_sejour->_id]);
    continue;
  }
}

$count_abs_allergie = 0;
foreach ($atcd_absence as $_atcd_absence) {
  if ($_atcd_absence->type == 'alle') {
    $count_abs_allergie++;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour_id", $sejour->_id);
$smarty->assign("antecedents_sejour", $dossier_medical_sejour->_ref_antecedents_by_type);
$smarty->assign("dossier_medical_sejour", $dossier_medical_sejour);
$smarty->assign("antecedents", $dossier_medical->_ref_antecedents_by_type);
$smarty->assign("dossier_medical", $dossier_medical);
$smarty->assign("patient", $patient);
$smarty->assign("sejours", $sejours);
$smarty->assign("atcd_absence", $atcd_absence);
$smarty->assign("count_abs_allergie", $count_abs_allergie);

$smarty->display("inc_antecedents_allergies.tpl");
