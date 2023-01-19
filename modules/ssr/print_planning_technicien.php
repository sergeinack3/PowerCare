<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCando::checkRead();
$kine_id      = CView::get("kine_id", "num");
$current_day  = CView::get("current_day", "bool default|0");
$day_used     = CView::get("day_used", "date default|now", true);
$surveillance = CView::get("surveillance", "bool default|0");
CView::checkin();

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("kine_id", $kine_id);
$smarty->assign("current_day", $current_day);
$smarty->assign("surveillance", $surveillance);
$smarty->display("print_planning_technicien");
