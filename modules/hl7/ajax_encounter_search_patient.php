<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CValue::getOrSession("patient_id");

$patient = new CPatient();
$patient->load($patient_id);
$patient->loadIPP();

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->display("inc_search_encounter.tpl");