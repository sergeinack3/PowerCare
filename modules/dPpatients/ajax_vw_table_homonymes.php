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
use Ox\Mediboard\Patients\CPatientSignature;

CCanDo::checkAdmin();

$start = CView::get("start", "num default|0");
$step  = CView::get("step", "num default|20");

CView::checkin();

$patient_signature = new CPatientSignature();
$homonymes         = $patient_signature->findHomonymes($start, $step);

$smarty = new CSmartyDP();
$smarty->assign("homonymes", $homonymes);
$smarty->assign("start_homonymes", $start);
$smarty->assign("step", $step);

$smarty->display("inc_identito_vigilance_tab_homonymes.tpl");