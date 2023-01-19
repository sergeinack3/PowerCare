<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::check();

$page         = CView::get("page", "num default|0");
$show_results = CView::get("show_results", "bool default|1");
$step         = CView::get("step", "num", true);
$total        = CView::get("total", "num", true);
$change_page  = CView::get("change_page", "str", true);

CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("current"     , $page);
$smarty->assign("show_results", $show_results);
$smarty->assign("step"        , $step);
$smarty->assign("total"       , $total);
$smarty->assign("change_page" , $change_page);
$smarty->display("inc_pagination");
