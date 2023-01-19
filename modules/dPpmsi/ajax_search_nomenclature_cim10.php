<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Pmsi\CCIM10;

CCanDo::checkRead();
$category_id    = CView::get("category_id", "ref class|CCIM10CategoryATIH");
$code    = CView::get("code", "str");
$words   = CView::get("words", "str");
$modal   = CView::get("modal", "str");
$current = CView::get("current", "num default|0");
CView::checkin();

$step          = 30;
$limit         = "$current, $step";

if ($code) {
  $where["code"] = "LIKE '%$code%'";
}
else {
  $where["code"] = "IS NOT NULL";
}

if ($category_id) {
  $where["category_id"] = "= '$category_id'";
}

$order         = "code";

/** @var CCIM10[] $list_cim */
$cim      = new CCIM10();
$list_cim = $cim->seek($words, $where, $limit, true, null, $order);
$total    = $cim->_totalSeek;

$smarty = new CSmartyDP();
$smarty->assign("cim"     , $cim);
$smarty->assign("list_cim", $list_cim);
$smarty->assign("current" , $current);
$smarty->assign("step"    , $step);
$smarty->assign("total"   , $total);
$smarty->assign("modal"   , $modal);
$smarty->display("nomenclature_cim/inc_search_nomenclature_cim10");