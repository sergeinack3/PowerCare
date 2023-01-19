<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CSociete;

CCanDo::checkRead();

$start    = CView::get("start", "num default|0");
$keywords = CView::get("keywords", "str");

$suppliers     = CView::get("suppliers", "bool");
$manufacturers = CView::get("manufacturers", "bool");
$inactive      = CView::get("inactive", "bool");

CView::setSession("suppliers", $suppliers);
CView::setSession("manufacturers", $manufacturers);
CView::setSession("inactive", $inactive);

CView::checkin();

if (!$keywords) {
  $keywords = "%";
}

$societe = new CSociete();
/** @var CSociete[] $list */
$list       = $societe->seek($keywords, null, 1000, true);
$list_count = $societe->_totalSeek;

foreach ($list as $_id => $_societe) {
  if (!($manufacturers && $_societe->_is_manufacturer
    || $suppliers && $_societe->_is_supplier
    || $inactive && (!$_societe->_is_supplier && !$_societe->_is_manufacturer))
  ) {
    unset($list[$_id]);
    $list_count--;
  }
}

$list = array_slice($list, $start, 30);

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('list', $list);
$smarty->assign('list_count', $list_count);
$smarty->assign('start', $start);

$smarty->display('inc_societes_list.tpl');

