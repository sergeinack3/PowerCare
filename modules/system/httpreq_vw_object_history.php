<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;

CCanDo::check();
$object_class = CView::get("object_class", "str");
$object_id    = CView::get("object_id", "ref class|$object_class");
$object_guid  = CView::get("object_guid", "str default|" . "$object_class-$object_id");

$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
    CAppUI::notFound($object_guid);
}
CView::checkin();
CView::enforceSlave();

$object->needsRead();

$log = new CUserLog();
$log->setObject($object);
$count = $log->countMatchingList();

$action                  = new CUserAction();
$action->object_class_id = $object->getObjectClassID();
$action->object_id       = $object->_id;
$action->_ref_object     = $object;

$count = $count + $action->countMatchingList();

/** @var CUserLog[] $logs */
$logs    = $log->loadMatchingList("date DESC", 10);
$actions = $action->loadMatchingList("date DESC", 10);

/** @var CUserAction $_action */
foreach ($actions as $_action) {
    $log = new CUserLog();
    $log->loadFromUserAction($_action);
    $logs[$log->_id] = $log;
}

foreach ($logs as $key => $_log) {
    $_log->loadRefUser();
}

$more = $count - count($logs);

CMbArray::pluckSort($logs, SORT_DESC, 'date');

$user = CUser::get();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("logs", $logs);
$smarty->assign("more", $more);
$smarty->assign("class_name", $object->_class);
$smarty->assign("class_id", $object->_id);
$smarty->assign("user_role", $user->user_type);

$smarty->display("vw_object_history.tpl");
