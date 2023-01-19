<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();
CView::checkin();

$cache = Cache::getCache(Cache::DISTR);
$keys  = $cache->list('index-');

$info_keys = [];
foreach ($keys as $_key) {
    if (preg_match('/\-infos$/', $_key)) {
        $info_keys[] = $_key;
    }
}

$indexes_infos = [];
if (!empty($info_keys)) {
    $indexes_infos = $cache->getMultiple($info_keys);
}

usort(
    $indexes_infos,
    function ($a, $b) {
        return strcmp($b['creation_datetime'], $a['creation_datetime']);
    }
);

$smarty = new CSmartyDP();
$smarty->assign('indexes_infos', $indexes_infos);
$smarty->display("vw_object_indexer");
