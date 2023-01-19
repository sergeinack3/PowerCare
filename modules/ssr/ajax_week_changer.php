<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkRead();
$date      = CView::get("date", "date default|now", true);
$view      = CView::get("view", "str", true);
$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
CView::checkin();

CAccessMedicalData::logAccess("CSejour-$sejour_id");

$week_number = date('W', strtotime($date));

$planning  = new CPlanningWeek($date);
$next_week = CMbDT::date("+1 week", $date);
$prev_week = CMbDT::date("-1 week", $date);

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("planning"   , $planning);
$smarty->assign('week_number', $week_number);
$smarty->assign("next_week"  , $next_week);
$smarty->assign("prev_week"  , $prev_week);
$smarty->assign("view"       , $view);
$smarty->assign("sejour_id"  , $sejour_id);
$smarty->display("inc_week_changer");
