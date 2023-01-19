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
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::getRefCheckEdit("patient_id", "ref class|CPatient");

CView::checkin();

$patient = CPatient::findOrFail($patient_id);

$smarty = new CSmartyDP();

$smarty->assign("types", array("Autre"));
$smarty->assign("repeat", null);
$smarty->assign("recall_age", null);
$smarty->assign("patient", $patient);
$smarty->assign("empty_injection", new CInjection());
$smarty->assign("label_read_only", 0);

$smarty->display("vaccination/multiple_vaccinations");
