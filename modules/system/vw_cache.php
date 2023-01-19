<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CacheInfo;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$smarty = new CSmartyDP();
$smarty->assign("shm_global_info", Cache::getInfo(Cache::OUTER));
$smarty->assign("dshm_global_info", Cache::getInfo(Cache::DISTR));
$smarty->assign("opcode_global_info", CacheInfo::getOpcodeCacheInfo());
$smarty->assign("assets_global_info", CacheInfo::getAssetsCacheInfo());
$smarty->display("vw_cache.tpl");
