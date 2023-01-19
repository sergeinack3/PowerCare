<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\AccessLog\CAccessLog;

CCanDo::checkRead();

$date      = CView::get("date", "date default|now", true);
$groupmod  = CView::get("groupmod", "str default|2", true);
$interval  = CView::get(
    "interval",
    "enum list|one-day|one-week|eight-weeks|one-year|four-years|twenty-years default|one-day",
    true
);
$user_type = CView::get("user_type", "enum list|0|1|2|3 default|0", true);

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

// Human/bot filter
$module = null;
if (!is_numeric($groupmod)) {
    $module   = $groupmod;
    $groupmod = 0;
}

$smarty = new CSmartyDP();

$smarty->assign("groupmod", $groupmod);

$smarty->assign("date", $date);
$smarty->assign("hours", $hours);
$smarty->assign("hour_min", $hour_min);
$smarty->assign("hour_max", $hour_max);

$smarty->assign("left_mode", $left_mode);
$smarty->assign("left_sampling", $left_sampling);

$smarty->assign("right_mode", $right_mode);
$smarty->assign("right_sampling", $right_sampling);

$smarty->assign("module", $module);
$smarty->assign("interval", $interval);
$smarty->assign("listModules", CModule::getInstalled());

$smarty->assign("user_type", $user_type);

$smarty->assign("left_modes", CAccessLog::$left_modes);
$smarty->assign("right_modes", CAccessLog::$right_modes);

$smarty->display("view_access_logs.tpl");
