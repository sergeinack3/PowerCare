<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CLogAccessMedicalData;
use Ox\Mediboard\System\CUserAuthentication;

CCanDo::checkEdit();

$date_min     = CView::get('_date_min', 'date', true);
$date_max     = CView::get('_date_max', 'date moreThan|_date_min', true);
$object_class = CView::get('object_class', 'str');
$object_id    = CView::get('object_id', 'ref class|CPatient');
$user_id      = CView::get('user_id', 'ref class|CMediusers');
$patient_id   = CView::get('patient_id', 'ref class|CMediusers');
$start        = (int)CView::get('start', 'num default|0');
$step         = (int)CView::get('step', 'num default|100');
$print        = CView::get('print', 'bool');
CView::checkin();
CView::enforceSlave();

$log_access = new CLogAccessMedicalData();

$ds = $log_access->getDS();

$where = [];

if ($object_class) {
    //Remove object_class filter in order to retrieve every logs
    //    $where['object_class'] = $ds->prepare("= ?", $object_class);
}

if ($object_id) {
    $where['object_id'] = $ds->prepare("= ?", $object_id);
}

if ($patient_id) {
    $where['object_id'] = $ds->prepare("= ?", $patient_id);
}

if ($user_id) {
    $where['user_id'] = "= '$user_id'";
}

if ($date_min) {
    $where[] = $ds->prepare('log_access_medical_data.datetime >= ?', "$date_min 00:00:00");
}

if ($date_max) {
    $where[] = $ds->prepare('log_access_medical_data.datetime <= ?', "$date_max 23:59:59");
}

$limit = "{$start}, {$step}";

/** @var CUserAuthentication[] $user_auths */

$request = new CRequest();
$request->addSelect('*, GROUP_CONCAT(context SEPARATOR ", ") AS context_concat');
$request->addTable($log_access->_spec->table);
$request->addWhere($where);
$request->addGroup('user_id, datetime, object_class, object_id');
$request->setLimit($limit);
$request->addOrder('access_id DESC');
$rows         = $ds->exec($request->makeSelect());
$log_accesses = [];
while ($_log_access = $ds->fetchObject($rows, 'CLogAccessMedicalData')) {
    $_log_access->loadTargetObject();
    $_log_access->loadRefUser();
    $_log_access->loadRefGroup();
    $log_accesses[] = $_log_access;
}
$total = count($log_accesses);

$smarty = new CSmartyDP();
$smarty->assign('print', $print);
$smarty->assign('start', $start);
$smarty->assign('step', $step);
$smarty->assign('total', $total);
$smarty->assign('log_accesses', $log_accesses);
$smarty->display('../../admin/templates/inc_vw_access_history.tpl');
