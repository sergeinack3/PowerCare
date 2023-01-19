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
use Ox\Core\Chronometer;
use Ox\Core\CObjectIndexer;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();
$index_name = CView::get('index_name', 'str notNull');
$tokens     = trim(CView::get('tokens', 'str'));
CView::checkin();

$cache = Cache::getCache(Cache::DISTR);

$index = $cache->get($index_name);

// Extract name and class from index name
$reversedParts = explode('-', strrev(str_replace('index-', '', $index_name)), 2);
[$class, $name] = array_map('strrev', $reversedParts);

$search_index = $index['index'];
$keys         = [];
// Manually filter keys to only show ones that match search token
// Actually done in CObjectIndexer::searchIndices
if ($tokens) {
    $exploded_tokens = explode(' ', $tokens);

    foreach ($search_index as $_key => $_values) {
        foreach ($exploded_tokens as $_token) {
            if ((($_key === $_token) || (strpos($_key, $_token) !== false))) {
                $keys[$_key] = count($_values);
            }
        }
    }
} else {
    foreach ($search_index as $_key => $_values) {
        $keys[$_key] = count($_values);
    }
}
arsort($keys);

$chrono = new Chronometer();
$chrono->start();
$object_indexer = new CObjectIndexer($name, $class, $index['version']);
$objects        = $object_indexer->search($tokens);
$chrono->stop();

if ($tokens) {
    $object_count = count($objects);
    $search_time  = round($chrono->total * 1000, 2);
    CAppUI::callbackAjax('ObjectIndexer.displayTiming', $object_count, $search_time);
}

$smarty = new CSmartyDP();
$smarty->assign('keys', $keys);
$smarty->assign('class', $class);
$smarty->assign('tokens', $tokens);
$smarty->assign('objects', $objects);
$smarty->assign('index_name', $index_name);
$smarty->display("inc_result_object_indexer");
