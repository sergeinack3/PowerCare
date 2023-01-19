<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

// Chargement de la prescription
$prescription = new CPrescriptionLabo;
if ($prescription->load(CValue::getOrSession("prescription_id"))) {
  $prescription->loadRefsBack();
  $prescription->loadClassification();
}

// Chargement du patient
$patient_id = CValue::first($prescription->patient_id, CValue::getOrSession("patient_id"));
$patient = new CPatient;
$patient->load($patient_id);
$patient->loadRefsPrescriptions(PERM_EDIT);

// Chargement de la première prescription dans le cas ou il n'y en a pas
if (!$prescription->_id && $patient->_id && count($patient->_ref_prescriptions)) {
  $prescription->load(reset($patient->_ref_prescriptions)->_id);
  $prescription->loadRefsBack();
  $prescription->loadClassification();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient"  , $patient);
$smarty->assign("prescription"  , $prescription);

$smarty->display("vw_resultats");
