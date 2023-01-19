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
use Ox\Core\CView;
use Ox\Mediboard\System\Purge\CObjectPurger;

CCanDo::checkAdmin();

$class_name  = CView::post(
    "class_name",
    "enum list|" . implode('|', array_keys(CObjectPurger::$allowed_classes)) . ' notNull'
);
$start       = CView::post("start", "num default|0");
$step        = CView::post("step", "num default|10");
$total_count = CView::post("total_count", "num default|0");
$max_id      = CView::post("max_id", "num");

CView::checkin();

CApp::setTimeLimit(300);

$purger = CObjectPurger::getPurger($class_name);
$result = $purger->purgeObjects($start, $step, $max_id);

// increment the start to avoid beeing stuck on non purgeable object
if ($result['ko']) {
    $start += count($result['ko']);
}

$count_purged = $total_count - count($result['ok']);

echo CAppUI::getMsg();

CAppUI::js("nextPurgeStep('$class_name', $start, $count_purged)");
