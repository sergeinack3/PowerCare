<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CLogAccessMedicalData;

CCanDo::checkEdit();

$date_min = CView::get('_date_min', 'date default|' . CMbDT::date('-1 month'), true);
$date_max = CView::get('_date_max', 'date default|now moreThan|_date_min', true);
CView::checkin();

$log_access = new CLogAccessMedicalData();
$log_access->_date_min = $date_min;
$log_access->_date_max = $date_max;

$smarty = new CSmartyDP();
$smarty->assign("log_access", $log_access);
$smarty->assign("print", 0);
$smarty->display("vw_access_history.tpl");
