<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();
$type = CView::get("type", "enum notNull list|shm|dshm");
$key  = CView::get("key", "str notNull");
$key  = stripslashes($key);

CView::checkin();

switch ($type) {
    default:
    case "shm":
        $cache = Cache::getCache(Cache::OUTER)->withCompressor();
        $value = $cache->get($key);
        break;
    case "dshm":
        $cache = Cache::getCache(Cache::DISTR)->withCompressor();
        $value = $cache->get($key);
        break;
}

$smarty = new CSmartyDP();
$smarty->assign('type', $type);
$smarty->assign('key', $key);
$smarty->assign('value', CMbArray::toJSON($value, true, JSON_PRETTY_PRINT));
$smarty->display('inc_vw_cache_entry_value.tpl');
