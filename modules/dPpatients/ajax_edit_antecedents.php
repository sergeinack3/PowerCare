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
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$patient_id    = CValue::get("patient_id");
$type          = CValue::get("type");
$antecedent_id = CValue::get("antecedent_id");
$callback      = CValue::get('callback', 0);

$patient = new CPatient();
$patient->load($patient_id);

$antecedents = array();

if ($type) {
  $dossier_medical = $patient->loadRefDossierMedical();
  /** @var CAntecedent[] $antecedents */
  $antecedents = $dossier_medical->loadRefsAntecedentsOfType($type);

  foreach ($antecedents as $_antecedent) {
    $_antecedent->updateOwnerAndDates();
  }
}

$antecedent = new CAntecedent();
$antecedent->load($antecedent_id);

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("antecedents", $antecedents);
$smarty->assign("is_anesth", CAppUI::$user->isAnesth());
$smarty->assign("antecedent", $antecedent);
$smarty->assign("type", $type);
if ($callback) {
  $smarty->assign('callback', $callback);
}
$smarty->display("inc_edit_antecedents.tpl");
