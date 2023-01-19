<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\Module\CModule;

if (!CAppUI::pref("allowed_modify_identity_status")) {
    CAppUI::accessDenied();
}

$smarty = new CSmartyDP();
$smarty->assign("date_min", CMbDT::dateTime("-2 DAY"));
$smarty->assign("date_max", CMbDT::dateTime());
$smarty->display("patient_state/vw_patient_state.tpl");
