<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

CView::checkin();

$host1 = '<std>';
$host2 = '<slave>';

$start_date = CMbDT::date() . ' 00:00:00';
$end_date   = CMbDT::dateTime();

$smarty = new CSmartyDP();
$smarty->assign('host1', $host1);
$smarty->assign('host2', $host2);
$smarty->assign('start_date', $start_date);
$smarty->assign('end_date', $end_date);
$smarty->display('vw_audit');
