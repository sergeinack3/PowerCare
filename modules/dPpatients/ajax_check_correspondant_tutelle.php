<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CValue::get("patient_id");
$tutelle    = CValue::get("tutelle");

$has_tutelle = 0;

if ($patient_id) {
  $patient = new CPatient();
  $patient->load($patient_id);

  $correspondants = $patient->loadRefsCorrespondantsPatient();

  foreach ($correspondants as $_correspondant) {
    if ($_correspondant->parente == "tuteur") {
      $has_tutelle = 1;
      break;
    }
  }
}

$smarty = new CSmartyDP;

$smarty->assign("has_tutelle", $has_tutelle);
$smarty->assign("tutelle", $tutelle);

$smarty->display("inc_check_correspondant_tutelle.tpl");
