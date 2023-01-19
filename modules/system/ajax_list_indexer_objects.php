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
$index_name = CView::get('index_name', 'str notNull');
$token      = CView::get('token', 'str');
CView::checkin();

$cache = Cache::getCache(Cache::DISTR);

$index   = $cache->get($index_name);
$objects = [];

$keys = array_map(
    function ($v) use ($index_name) {
        return "$index_name-object-$v";
    },
    array_keys($index['index'][$token])
);

$objects = $cache->getMultiple($keys);
foreach ($objects as &$_object) {
    $_object['pertinence'] = "";
}

$smarty = new CSmartyDP();
$smarty->assign('class', $index['class']);
$smarty->assign('tokens', explode(' ', $token));
$smarty->assign('objects', $objects);
$smarty->display("inc_list_indexer_objects");
