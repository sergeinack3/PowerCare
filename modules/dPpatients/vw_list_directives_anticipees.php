<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();

$patient_id = CView::get("patient_id", "ref class|CPatient", true);
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$correspondants = $patient->loadRefsCorrespondantsPatient();
$directives     = $patient->loadRefsDirectivesAnticipees();

foreach ($directives as $_directive) {
  $_directive->loadRefDetenteur();
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("directives", $directives);
$smarty->display("inc_list_directives_anticipees.tpl");
