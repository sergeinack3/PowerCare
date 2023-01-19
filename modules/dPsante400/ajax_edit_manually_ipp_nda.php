<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_guid  = CValue::get("sejour_guid");
$patient_guid = CValue::get("patient_guid");
/** @var CSejour $sejour */
$sejour = CMbObject::loadFromGuid($sejour_guid);
/** @var CPatient $patient */
$patient = CMbObject::loadFromGuid($patient_guid);

$sejour->loadNDA();
$patient->loadIPP();

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("patient", $patient);
$smarty->display("inc_edit_manually_ipp_nda.tpl");