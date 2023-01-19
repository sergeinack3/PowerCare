<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;

/**
 * view of the complete patient data (only)
 */
CCanDo::checkRead();

$pat_id = CValue::get("patient_id");

$patient = new CPatient();
$patient->load($pat_id);
$patient->loadComplete();
$patient->countINS();

//smarty
$smarty = new CSmartyDP();
$smarty->assign("object", $patient);
$smarty->display("CPatient_complete.tpl");