<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;

CCanDo::checkAdmin();
$type = CView::get("type", "enum notNull list|shm|dshm");
$key  = CView::get("key", "str notNull");
CView::checkin();

$key = str_replace('\\\\', '\\', $key);

$job_done = false;
switch ($type) {
    default:
    case "shm":
        $cache    = Cache::getCache(Cache::OUTER);
        $job_done = $cache->delete($key);
        break;
    case "dshm":
        $cache    = Cache::getCache(Cache::DISTR);
        $job_done = $cache->delete($key);
        break;
}

$job_done ?
    CAppUI::setMsg("System-msg-Cache entry removed", UI_MSG_OK) :
    CAppUI::setMsg("System-error-Error during suppression", UI_MSG_ERROR);

echo CAppUI::getMsg();
