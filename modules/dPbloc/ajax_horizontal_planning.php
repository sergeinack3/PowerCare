<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CHorizontalPlanning;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$date             = CView::get('date', 'date default|now', true);
$blocs_ids        = CView::get('blocs_ids', 'str', true);
$salles_ids       = CView::get('salles_ids', 'str', true);
$selected_period  = CView::get('selected_period', 'str');
$window_width     = CView::get('window_width', 'num');

CView::checkin();

$salles_ids = $salles_ids != '' ? explode('|', $salles_ids) : array();

$group = CGroups::loadCurrent();

$planning = new CHorizontalPlanning($date, $blocs_ids, $salles_ids, $window_width);
$salles   = $planning->getPlanningData();
$periods  = $planning->getPeriods();
$time     = $planning->getCurrentTimePosition();
$height   = $planning->getHeight();

if ($selected_period != null) {
  $time['period'] = $selected_period;
}

$user = CMediusers::get();
$user->isPraticien();

$smarty = new CSmartyDP();
$smarty->assign('periods',          $periods);
$smarty->assign('salles',           $salles);
$smarty->assign('time',             $time);
$smarty->assign('user',             $user);
$smarty->assign('height',           $height);
$smarty->assign('move_operations',  CCanDo::edit() && CAppUI::pref('drag_and_drop_horizontal_planning') == '1');
$smarty->display('inc_horizontal_planning.tpl');