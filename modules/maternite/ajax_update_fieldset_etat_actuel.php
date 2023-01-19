<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient");
$light_view = CView::get("light_view", "bool default|0");
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$patient->loadLastGrossesse();
$patient->loadLastAllaitement();

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("light_view", $light_view);

$smarty->display("inc_fieldset_etat_actuel");
