<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CExamIgs;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour_id   = CValue::get("sejour_id");
$exam_igs_id = CValue::get("exam_igs_id");
$date        = CValue::get("date", CMbDT::dateTime());
$digest      = CValue::get("digest");

$_SESSION['soins']["selected_tab"] = "score_igs";

// Chargement du séjour
$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Chargement du patient
$sejour->loadRefPatient();
$patient = $sejour->_ref_patient;

$exam_igs = new CExamIgs();

$last_constantes = array();

if ($exam_igs_id) {
  $exam_igs->load($exam_igs_id);
}
else {
  $cconstantes = new CConstantesMedicales();

  $exam_igs = $cconstantes->calculateIGSScore($patient, $date, $sejour);
  $exam_igs->date = $date;

  list($constantes_medicales, $date) = CConstantesMedicales::getLatestFor($patient, CMbDT::dateTime());

  $last_constantes["FC"] = $constantes_medicales->pouls;
  $last_constantes["TA"] = ($constantes_medicales->ta) ? explode("|", $constantes_medicales->ta)[0] * 10 : null; // Systolic * 10
  $last_constantes["glasgow"] = $constantes_medicales->glasgow;
  $last_constantes["temperature"] = $constantes_medicales->temperature;
  $last_constantes["diurese"] = $constantes_medicales->_diurese;

}

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("exam_igs", $exam_igs);
$smarty->assign("last_constantes", $last_constantes);
$smarty->assign("digest", $digest);
$smarty->display("exam_igs");
