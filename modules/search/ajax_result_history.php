<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Search\CSearch;
use Ox\Mediboard\Search\CSearchHistory;

CCanDo::checkRead();

$contextes = CView::get("contextes", "str");

CView::checkin();

$user       = CMediusers::get();
$date_limit = CMbDT::dateTime('-1 week');

$search_history    = new CSearchHistory();
$where             = array('user_id' => '=' . $user->user_id);
$contextes         = ($contextes) ? $contextes : CSearch::$contextes;
$where["contexte"] = $search_history->_spec->ds->prepareIn($contextes);
$where["date"]     = " >= '$date_limit' ";

$search_historys = $search_history->loadList($where, 'date DESC');


$types = array();
$group = CGroups::loadCurrent();
if ($search_types = CAppUI::conf("search active_handler active_handler_search_types", $group)) {
  $types = explode("|", $search_types);
}

$smarty = new CSmartyDP();
$smarty->assign('search_historys', $search_historys);
$smarty->assign('search_history', $search_history);
$smarty->assign('types', $types);
$smarty->display("inc_results_history.tpl");


