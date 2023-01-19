<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Cache;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();
$error = '';

$cache = Cache::getCache(Cache::OUTER);

$smarty = new CSmartyDP();
$smarty->assign('cache', $cache);
$smarty->assign('error', $error);
$smarty->display('inc_configure_serveur');
