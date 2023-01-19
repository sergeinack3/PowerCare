<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CObjectClass;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::check();

global $dialog;

if (!CCanDo::read() && !$dialog) {
    global $can;
    $can->denied();
}

if (!CAppUI::pref('system_show_history')) {
    CAppUI::accessDenied();
}

$filter = new CUserLog();

$filter->object_class = CView::get("object_class", "str", !$dialog);
$ex_class_id          = CView::get("ex_class_id", "num");

$object_class_name = $filter->object_class;
if ($filter->object_class == 'CExObject') {
    $object_class_name .= '_' . $ex_class_id;
}

$filter->object_class_id = $filter->object_class ? CObjectClass::getID($object_class_name) : null;
$filter->object_id       = CView::get("object_id", "num", !$dialog); // Can be a deleted object

CView::checkin();
CView::enforceSlave();

$object = new CStoredObject();
if ($filter->object_id && $filter->object_class) {
    /** @var CStoredObject $object */
    $object = new $filter->object_class;

    $ex_object_history = false;
    if ($ex_class_id && $filter->object_class === "CExObject") {
        /** @var CExObject $object */
        $object->_ex_class_id = $ex_class_id;
        $object->setExClass();
        $filter->object_class .= "_$ex_class_id";
        $ex_object_history    = true;
    }

    $object->load($filter->object_id);

    // CExClass::inHermeticMode() handling
    if ($ex_object_history && !$object->getPerm(PERM_READ)) {
        CAppUI::accessDenied();
    }

    if (!$ex_class_id && !$object->getPerm(PERM_READ)) {
        CAppUI::accessDenied();
    }

    $object->loadHistory();
}

$filter->loadRefUser();

// Récupération des logs correspondants
$where        = [];
$where_log    = [];
$where_action = [];


switch ($object->_class) {
    case "CCompteRendu":
        /** @var CCompteRendu $object */

        // Inclusion des logs sur l'objet CContentHTML
        $object->loadContent(false);
        $content = $object->_ref_content;

        // To activate force index below
        $where["object_id"] = "IN ('$filter->object_id', '$content->_id')";

        // Actual query
        $where_log[] = "
    (object_id = '$filter->object_id' AND object_class = '$filter->object_class') OR
    (object_id = '$content->_id'      AND object_class = 'CContentHTML')";

        $object_class_id = CObjectClass::getID('CContentHTML');
        $where_action[]  = "
    (object_id = '$filter->object_id' AND object_class_id = '$filter->object_class_id') OR
    (object_id = '$content->_id'      AND object_class_id = '$object_class_id')";
        break;

    case "CUserLog":
        $where_log["user_log_id"] = "= '$filter->object_id'";
        break;

    case "CUserAction":
        $where_action["user_action_id"] = "= '$filter->object_id'";
        break;

    default:
        if ($filter->object_id) {
            $where["object_id"] = "= '$filter->object_id'";
        }
        if ($filter->object_class) {
            $where_log["object_class"]       = "= '$filter->object_class'";
            $where_action["object_class_id"] = "= '$filter->object_class_id'";
        }
}


// merge where array
$where_log    = array_merge($where, $where_log);
$where_action = array_merge($where, $where_action);

$log    = new CUserLog();
$action = new CUserAction();

$list       = null;
$list_count = null;

$is_admin = CCanDo::admin();

$dossiers_medicaux_shared = CAppUI::conf("dPetablissement dossiers_medicaux_shared");


// CUserLog
$list_log = $log->loadList($where_log, "date DESC", "0, 100", null, null, "object_id");
CStoredObject::massLoadFwdRef($list_log, "object_id");

// CUserAction
$list_action = $action->loadList($where_action, "date DESC", "0, 100", null, null, "object_ref");
CStoredObject::massLoadFwdRef($list_action, "object_id");
CStoredObject::massLoadBackRefs($list_action, "user_action_datas");

// merge log & action
foreach ($list_action as $_user_action) {
    $_user_action->loadRefUserActionDatas();
    $_user_log = new CUserLog();
    $_user_log->loadFromUserAction($_user_action);
    $list_log[$_user_log->_id] = $_user_log;
}

$list = $list_log;

// Sort by id with PHP cuz dumbass MySQL won't limit rowscan before sorting
// even though `date` is explicit as first intention sorter AND obvious index in most cases
// Tends to be a known limitation
$ordered_list = CMbArray::pluck($list, "_id");
array_multisort($ordered_list, SORT_DESC, $list);

$list_count = $log->countList($where_log, null, null, "object_id");
$list_count += $action->countList($where_action, null, null, "object_ref");

$group_id = CGroups::loadCurrent()->_id;
$users    = CStoredObject::massLoadFwdRef($list, "user_id");

// Mass loading des mediusers et des fonctions
$mediuser  = new CMediusers();
$mediusers = $mediuser->loadList(["user_id" => CSQLDataSource::prepareIn(array_keys($users))]);
CStoredObject::massLoadFwdRef($mediusers, "function_id");


foreach ($list as $_log) {
    $_log->loadRefUser();

    $function = isset($mediusers[$_log->user_id]) ?
        $mediusers[$_log->user_id]->loadRefFunction() :
        $_log->_ref_user->loadRefMediuser()->loadRefFunction();

    if (!$is_admin && !$dossiers_medicaux_shared && $function->group_id != $group_id) {
        unset($list[$_log->_id]);
        continue;
    }

    $_log->loadTargetObject();
    $_log->getOldValues();
    $_log->undiff_old_Values();
}


// Tpl
$smarty = new CSmartyDP();
$smarty->assign("dialog", $dialog);
$smarty->assign("filter", $filter);
$smarty->assign("object", $object);
$smarty->assign("list", $list);
$smarty->assign("list_count", $list_count);
$smarty->display('view_history_object');
