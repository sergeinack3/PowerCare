<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;
use Ox\Core\Chronometer;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$purge = CView::get("purge", "bool default|0");

CView::checkin();

$chrono = new Chronometer();
$chrono->start();

$cache = Cache::getCache(Cache::OUTER);

if ($purge) {
    $cache->delete("mediusers");
    $chrono->step("purge");
}

if (!$cache->has("mediusers")) {
    $chrono->step("acquire (not yet)");
    $mediuser  = new CMediusers();
    $mediusers = $mediuser->loadListFromType();
    $chrono->step("load");
    $cache->set("mediusers", $mediusers);
    $chrono->step("put");
}

/** @var CMediusers[] $mediusers */
$mediusers = $cache->get("mediusers");
$chrono->step("get");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("mediusers", $mediusers);
$smarty->assign("chrono", $chrono);
$smarty->display("cache_tester_users.tpl");

