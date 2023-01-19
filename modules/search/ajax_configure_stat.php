<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Search\CSearch;

CCanDo::checkAdmin();

// Client
$search = new CSearch();
try {
  $search->state();
}
catch (Throwable $e) {
  CAppUI::stepAjax("mod-search-indisponible", UI_MSG_ERROR);
}

$stats = $search->getStats();
// format json
foreach ($stats as $key => $stat) {
  $stats[$key] = CMbArray::toJSON($stat, true, JSON_PRETTY_PRINT);
}

$smarty = new CSmartyDP();
$smarty->assign("stats", $stats);
$smarty->assign("index_name", $search->_index);
$smarty->display("inc_configure_stat.tpl");