<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\AccessLog\CAccessLog;
use Ox\Mediboard\System\AccessLog\CAccessLogArchive;

CCanDo::checkRead();

$date      = CView::get("date", "date default|now", true);
$groupmod  = CView::get("groupmod", "str default|2", true);
$interval  = CView::get("interval", "enum list|one-day|one-week|eight-weeks|one-year|four-years|twenty-years default|one-day", true);
$user_type = CView::get("user_type", "enum list|0|1|2|3 default|0", true);
$bigsize   = CView::get('bigsize', 'str');

// Hour range for daily stats
$hour_min = CView::get("hour_min", "num default|6", true);
$hour_max = CView::get("hour_max", "num default|22", true);
$hours    = range(0, 24);

// Modes
$left_mode  = CView::get("left_mode", "enum list|" . implode("|", CAccessLog::$left_modes), true);
$right_mode = CView::get("right_mode", "enum list|" . implode("|", CAccessLog::$right_modes), true);

// Samplings
$left_sampling  = CView::get("left_sampling", "enum list|total|mean default|mean", true);
$right_sampling = CView::get("right_sampling", "enum list|total|mean default|total", true);


CView::checkin();
CView::enforceSlave();

$module = null;
if (!is_numeric($groupmod)) {
    $module   = $groupmod;
    $groupmod = 0;
}

$to = CMbDT::date("+1 DAY", $date);
switch ($interval) {
    default:
    case "one-day":
        $today = CMbDT::date("-1 DAY", $to);
        // Hours limitation
        $from = CMbDT::dateTime("+$hour_min HOUR", $today);
        $to   = CMbDT::dateTime("+$hour_max HOUR -1 MINUTE", $today);
        break;
    case "one-week":
        $from = CMbDT::date("-1 WEEK", $to);
        break;

    case "eight-weeks":
        $from = CMbDT::date("-8 WEEKS", $to);
        break;

    case "one-year":
        $from = CMbDT::date("-1 YEAR", $to);
        break;

    case "four-years":
        $from = CMbDT::date("-4 YEARS", $to);
        break;

    case "twenty-years":
        $from = CMbDT::date("-20 YEARS", $to);
        break;
}

$graphs = [];
$left   = [$left_mode, $left_sampling];
$right  = [$right_mode, $right_sampling];

switch ($groupmod) {
    case 0:
    case 1:
        $access_logs  = CAccessLog::loadAggregation($from, $to, $groupmod, $module, $user_type);
        $archive_logs = CAccessLogArchive::loadAggregation($from, $to, $groupmod, $module, $user_type);
        $logs         = array_merge($access_logs, $archive_logs);
        break;

    default:
    case 2:
        $logs = [new CAccessLog()];
        break;
}

$graphs_by_module = [];
foreach ($logs as $log) {
    switch ($groupmod) {
        case 0:
            $_graph = call_user_func(
                [get_class($log), "graphAccessLog"],
                $log->_module,
                $log->_action,
                $from,
                $to,
                $interval,
                $left,
                $right,
                $user_type
            );

            if (!isset($graphs_by_module[$log->_module . "-" . $log->_action])) {
                // 1st iteration => graph initialisation
                $graphs_by_module[$log->_module . "-" . $log->_action] = $_graph;
            } else {
                // We are in 'hits' mode, which means that we have to compute the average 'data' by hits
                // for each graphic
                if ($right_mode == 'hits') {
                    $graphs_by_module = CAccessLog::combineGraphs($groupmod, $graphs_by_module, $_graph, $log);
                }

                $graphs_by_module[$log->_module . "-" . $log->_action]["datetime_by_index"] +=
                    $_graph["datetime_by_index"];
            }
            break;

        case 1:
            $_graph = call_user_func(
                [get_class($log), "graphAccessLog"],
                $log->_module,
                null,
                $from,
                $to,
                $interval,
                $left,
                $right,
                $user_type
            );

            if (!isset($graphs_by_module[$log->_module])) {
                // 1st iteration => graph initialisation
                $graphs_by_module[$log->_module] = $_graph;
            } else {
                // We are in 'hits' mode, which means that we have to compute the average 'data' by hits
                // for each graphic
                if ($right_mode == 'hits') {
                    $graphs_by_module = CAccessLog::combineGraphs($groupmod, $graphs_by_module, $_graph, $log);
                }

                $graphs_by_module[$log->_module]["datetime_by_index"] += $_graph["datetime_by_index"];
            }

            break;

        default:
        case 2:
            $_graph         = CAccessLog::graphAccessLog(null, null, $from, $to, $interval, $left, $right, $user_type);
            $_archive_graph = CAccessLogArchive::graphAccessLog(
                null,
                null,
                $from,
                $to,
                $interval,
                $left,
                $right,
                $user_type
            );

            // We are in 'hits' mode, which means that we have to compute the average 'data' by hits for each graphic
            if ($right_mode == 'hits') {
                $_graph = CAccessLog::combineGraphs($groupmod, $_graph, $_archive_graph);
            }

            $_graph["datetime_by_index"] += $_archive_graph["datetime_by_index"];

            $graphs[] = $_graph;
            break;
    }
}

// Module graphs
if (in_array($groupmod, [0, 1])) {
    $graphs = [];
    foreach ($graphs_by_module as $_graph) {
        $graphs[] = $_graph;
    }
}

// Ajustements cosmétiques
foreach ($graphs as &$_graph) {
    $index = 0;
    foreach ($_graph["series"] as &$_series) {
        if (isset($_series["lines"])) {
            $_series["points"] = [
                "show"      => true,
                "radius"    => 2,
                "lineWidth" => 1,
            ];
        }

        foreach ($_series["data"] as &$_data) {
            if ($_data[1] === 0) {
                $_data[1] = null;
            }
        }
        $index++;
    }
}

$smarty = new CSmartyDP();
$smarty->assign("graphs", $graphs);
$smarty->assign("groupmod", $groupmod);
$smarty->assign("interval", $interval);
$smarty->assign('bigsize', $bigsize);
$smarty->assign('user_type', $user_type);
$smarty->assign('module', $module);
$smarty->display("vw_graph_access_logs");
