<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CObjectClass;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::check();

global $dialog;

if (!CCanDo::read() && !$dialog) {
    global $can;
    $can->denied();
}

$user  = CMediusers::get();
$mutex = new CMbMutex('system_view_history_' . $user->_id);
if (!$mutex->lock(60)) {
    CAppUI::stepAjax('CMbMutex-Error-Mutex is already present for this resource', UI_MSG_ERROR);
}

$start    = CView::get("start", "num default|0");
$stats    = CView::get("stats", "bool default|0");
$interval = CView::get("interval", "enum list|one-week|eight-weeks|one-year|four-years");
$csv      = CView::get("csv", "bool default|0");

$filter = new CUserLog();

$filter->_date_min       = CView::get("_date_min", "dateTime", !$dialog);
$filter->_date_max       = CView::get("_date_max", "dateTime", !$dialog);
$filter->user_id         = CView::get("user_id", "ref class|CUser", !$dialog);
$filter->object_class    = CView::get("object_class", "str", !$dialog);
$filter->object_class_id = $filter->object_class ? CObjectClass::getID($filter->object_class) : null;
$filter->object_id       = CView::get("object_id", "num", !$dialog); // Can be a deleted object
$filter->type            = CView::get("type", "enum list|" . $filter->_specs["type"]->list, !$dialog);
$ex_class_id             = CView::get("ex_class_id", "num");
$only_list               = CView::get("only_list", "bool");

CView::checkin();
CView::enforceSlave();

// Limit to a default one month for no context queries
if (!$filter->_date_min && !$filter->object_id && !$filter->user_id && !$ex_class_id) {
    $filter->_date_min = CMbDT::dateTime("-1 WEEK");
}

$object = new CStoredObject();
if ($filter->object_id && $filter->object_class) {
    /** @var CStoredObject $object */
    $object = new $filter->object_class;

    if ($ex_class_id && $filter->object_class === "CExObject") {
        /** @var CExObject $object */
        $object->_ex_class_id = $ex_class_id;
        $object->setExClass();
        $filter->object_class .= "_$ex_class_id";
    }

    $object->load($filter->object_id);

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

if ($filter->user_id) {
    $where["user_id"] = "= '$filter->user_id'";
}

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

if ($filter->type) {
    $where["type"] = "= '$filter->type'";
}
if ($filter->_date_min) {
    $where[] = "date >= '$filter->_date_min'";
}
if ($filter->_date_max) {
    $where[] = "date <= '$filter->_date_max'";
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

$max_execution_time = CAppUI::conf('system max_log_duration');

if (!$stats) {
    // CUserLog
    $list_log
        = $log->loadList($where_log, "date DESC", "$start, 100", null, null, null, null, true, $max_execution_time);
    CStoredObject::massLoadFwdRef($list_log, "object_id");

    // CUserAction
    $list_action = $action->loadList(
        $where_action,
        "date DESC",
        "$start, 100",
        null,
        null,
        null,
        null,
        true,
        $max_execution_time
    );
    CStoredObject::massLoadFwdRef($list_action, "object_id");


    // merge log & action
    foreach ($list_action as $_user_action) {
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

    $list_count = $log->countList($where_log, null, null, null, true, $max_execution_time);
    $list_count += $action->countList($where_action, null, null, null, true, $max_execution_time);

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
    }
}


if ($csv) {
    ob_clean();
    $date = CMbDT::dateTime();
    header("Content-type: text/csv");
    header('Content-Type: text/html;charset=ISO-8859-1');
    header("Content-disposition: attachment; filename='journal_utilisateur_$date-$filter->type.csv'");
    $fp           = fopen("php://output", "w");
    $csv_writer   = new CCSVFile($fp); // Use PROFILE_EXCEL as default
    $column_names = [
        "Classe",
        "Id",
        "IP",
        "Utilisateur",
        "Date",
        "Type",
        "Champs",
    ];
    $csv_writer->setColumnNames($column_names);
    $csv_writer->writeLine($column_names);
    foreach ($list as $_log) {
        $ref_object     = $_log->_ref_object;
        $line           = [];
        $line["Classe"] = CAppUI::tr($_log->object_class);
        if ($ref_object->_id) {
            $line["Id"] = $ref_object;
        } else {
            $line["Id"] = '-';
        }
        $line["IP"]          = $_log->ip_address ? inet_ntop($_log->ip_address) : null;
        $line["Utilisateur"] = $_log->_ref_user->_view;
        $line["Date"]        = $_log->date;
        $line["Type"]        = CAppUI::tr($_log->type);
        $object_csv          = "";
        if ($object->_id) {
            foreach ($_log->_fields as $_field) {
                if (array_key_exists($_field, $object->_specs)) {
                    $object_csv .= CAppUI::tr($object->$_field);
                } else {
                    $object_csv .= CAppUI::tr('CMbObject.missing_spec');
                }

                if (array_key_exists($_field, $_log->_old_values)) {
                    $object_csv .= $object->$_field;
                }
            }
        } else {
            if (strpos($_log->object_class, "CExObject_") === false && is_array($_log->_fields)) {
                foreach ($_log->_fields as $_field) {
                    if (array_key_exists($_field, $ref_object->_specs)) {
                        $object_csv .= CAppUI::tr($_log->object_class . '-' . $_field) . " - ";
                    } else {
                        $object_csv .= CAppUI::tr('CMbObject.missing_spec') . "($_field)";
                    }
                }
            }
        }
        $line["Champs"] = $object_csv;
        $csv_writer->writeLine($line);
    }

    $mutex->release();
    CApp::rip();
}

$mutex->release();

$smarty = new CSmartyDP($stats ? 'modules/dPstats' : 'modules/system');
$smarty->assign("dialog", $dialog);
$smarty->assign("filter", $filter);
$smarty->assign("object", $object);
$smarty->assign("list", $list);
$smarty->assign("start", $start);
$smarty->assign("list_count", $list_count);
$smarty->assign("stats", $stats);
$smarty->assign("csv", $csv);
$smarty->assign("interval", $interval);

if ($stats) {
    CAppUI::requireModuleFile('stats', 'graph_userlog');

    if (!$filter->_date_max) {
        $filter->_date_max = CMbDT::dateTime();
    }

    $graph =
        graphUserLog(
            $filter->_date_max,
            $interval,
            $filter->user_id,
            $filter->type,
            $filter->object_class,
            $filter->object_id
        );

    $smarty->assign("graph", $graph);
}

$template = 'view_history';

if ($stats) {
    $template = 'inc_graph_user_logs';
} elseif ($only_list) {
    $template = 'inc_view_history';
}

$smarty->display($template);
