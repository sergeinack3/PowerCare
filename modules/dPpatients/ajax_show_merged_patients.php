<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CObjectClass;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;

if (!CAppUI::pref("allowed_modify_identity_status")) {
    CAppUI::accessDenied();
}

$date = CValue::get('date');
$ids  = CValue::get('ids');

if (!$ids || !$date) {
    CAppUI::stepAjax('common-error-Missing parameter', UI_MSG_ERROR);
}

$patient_ids = explode('-', $ids);
$date        = preg_replace('/(\d\d)\/(\d\d)\/(\d\d\d\d)/', '\\3-\\2-\\1', $date);

if (!$patient_ids || !$date) {
    CAppUI::stepAjax('common-error-Invalid parameter', UI_MSG_ERROR);
}

$user_log = new CUserLog();
$ds       = $user_log->getDS();

$where = [
    'object_class' => "= 'CPatient'",
    'object_id'    => $ds->prepareIn($patient_ids),
    'type'         => "= 'merge'",
    'date'         => $ds->prepare("BETWEEN ?1 AND ?2", "$date 00:00:00", "$date 23:59:59"),
];

$logs = $user_log->loadList($where, null, null, 'object_id');

foreach ($logs as $_log) {
    $_log->loadView();

    /** @var CPatient $patient */
    $patient      = $_log->_ref_object;
    $identifiants = $patient->loadBackRefs('identifiants');

    /** @var CIdSante400 $_id */
    foreach ($identifiants as $_id) {
        $_id->getSpecialType();
    }
}

$user_action = new CUserAction();

$where = [
    'object_class_id' => $ds->prepare('= ?', CObjectClass::getID('CPatient')),
    'object_id'       => $ds->prepareIn($patient_ids),
    'type'            => "= 'merge'",
    'date'            => $ds->prepare("BETWEEN ?1 AND ?2", "$date 00:00:00", "$date 23:59:59"),
];

$action_logs = $user_action->loadList($where, null, null, 'object_id');
foreach ($action_logs as $_log) {
    $_log->loadView();
    $_log->getOldValues();

    /** @var CPatient $patient */
    $patient      = $_log->_ref_object;
    $identifiants = $patient->loadBackRefs('identifiants');

    /** @var CIdSante400 $_id */
    foreach ($identifiants as $_id) {
        $_id->getSpecialType();
    }
}

// Not array_merge because we want to preserve key association
$logs = $logs + $action_logs;

$smarty = new CSmartyDP();
$smarty->assign('date', $date);
$smarty->assign('logs', $logs);
$smarty->assign('logs_count', count($logs));
$smarty->display("patient_state/vw_merged_patients_details.tpl");
