<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id    = CValue::get("sejour_id");
$patient_id   = CValue::get("patient_id");

// Chargement du patient
$patient = new CPatient;
$patient->load($patient_id);
$patient->loadRefsCorrespondantsPatient();

// On récupére le séjour
$sejour = new CSejour();
if ($sejour_id) {
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  // On vérifie que l'utilisateur a les droits sur le sejour
  if (!$sejour->_canRead) {
    global $m, $tab;
    CAppUI::setMsg("Vous n'avez pas accés à ce séjour", UI_MSG_WARNING);
    CAppUI::redirect("m=$m&tab=$tab&sejour_id=0");
  }
  $patient = $sejour->_ref_patient;
}
else {
  $sejour->patient_id = $patient->_id;
  $sejour->_ref_patient = $patient;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejour"  , $sejour);
$smarty->assign("patient" , $patient);
$smarty->assign("form"    , "editSejour");

$smarty->display("inc_vw_assurances");
