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
use Ox\Core\CSmartyDP;

CCanDo::checkRead();

$user             = CAppUI::$user;
$latest_cache_key = "$user->_guid-latest_cache";

$cache = Cache::getCache(Cache::OUTER)->withCompressor();

$latest_cache = $cache->get($latest_cache_key);
foreach ($latest_cache["hits"] as &$keys) {
    ksort($keys);
}

$smarty = new CSmartyDP();
$smarty->assign("all_layers", Cache::getAllLayers());
$smarty->assign("latest_cache", $latest_cache);
$smarty->display("latest_cache_hits.tpl");

