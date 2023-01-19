<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CLongRequestLog;

CCanDo::checkAdmin();

CView::checkin();

CView::enableSlave();

// Filtering
$filter                      = new CLongRequestLog();
$filter->_datetime_start_min = CMbDT::date('-1 week') . ' 00:00:00';
$filter->_user_type          = 'all';

// Ranking
$date      = CMbDT::date();
$group_mod = 1;
$interval  = 'day';
$limit     = 6;
$threshold = 5;

$user           = new CUser();
$user->template = '0';
$order          = 'user_last_name, user_first_name';
$user_list      = $user->loadMatchingList($order);

$modules = CModule::getInstalled();

foreach ($modules as $_module) {
  $_module->updateFormFields();
}

uasort(
  $modules,
  function ($a, $b) {
    return strcmp(CMbString::removeAccents($a->_view), CMbString::removeAccents($b->_view));
  }
);

$smarty = new CSmartyDP();
$smarty->assign('user_list', $user_list);
$smarty->assign('modules', $modules);
$smarty->assign('filter', $filter);
$smarty->assign('date', $date);
$smarty->assign('group_mod', $group_mod);
$smarty->assign('interval', $interval);
$smarty->assign('limit', $limit);
$smarty->assign('threshold', $threshold);
$smarty->display('vw_long_request_logs.tpl');
