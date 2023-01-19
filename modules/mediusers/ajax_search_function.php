<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * View functions
 */
CCanDo::checkRead();

$page          = CView::get('page', 'num default|0');
$inactif       = CView::get("inactif", 'str');
$type          = CView::get("type", 'str');
$filter        = CView::get("filter", 'str', true);
$order_way     = CView::get("order_way", "str default|ASC", true);
$order_col     = CView::get("order_col", "str default|text", true);
CView::checkin();

$step = 25;
$group = CGroups::loadCurrent();

if ($type) {
  $where["type"] = "= '$type'";
}

if ($inactif == "1") {
  $where["actif"] = "= '0'";
}
if ($inactif == "0") {
  $where["actif"] = "= '1'";
}

$where["group_id"] = "= '$group->_id'";

$order = null;
if ($order_col == "text") {
  $order = "text $order_way";
}
if ($order_col == "type") {
  $order = "type $order_way, text ASC";
}

$function = new CFunctions();
if ($filter) {
  $functions       = $function->seek($filter, $where, "$page, $step", true, null, $order);
  $total_functions = $function->_totalSeek;
}
else {
  $functions       = $function->loadList($where, $order, "$page, $step");
  $total_functions = $function->countList($where);
}

foreach ($functions as $_function) {
  $_function->countBackRefs("users");
  $_function->countBackRefs("secondary_functions");
}


// Création du template
$smarty = new CSmartyDP();
$smarty->assign("inactif"            , $inactif);
$smarty->assign("functions"          , $functions);
$smarty->assign("total_functions"    , $total_functions);
$smarty->assign("page"               , $page);
$smarty->assign("function_id"        , CMediusers::get()->function_id);
$smarty->assign("type"               , $type);
$smarty->assign("order_way"          , $order_way);
$smarty->assign("order_col"          , $order_col);
$smarty->assign("step"               , $step);

$smarty->display("inc_search_functions.tpl");
