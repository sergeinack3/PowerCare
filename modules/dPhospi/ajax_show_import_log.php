<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$log_ok  = CView::get('log_ok', 'str');
$log_err = CView::get('log_err', 'str');

CView::checkin();

$log_ok  = $log_ok ?: array();
$log_err = $log_err ?: array();

$ok = array();
foreach ($log_ok as $_log) {
  $ok[] = str_replace('\\', '', $_log);
}

$err = array();
foreach ($log_err as $_log) {
  $err[] = str_replace('\\', '', $_log);
}

$smarty = new CSmartyDP();
$smarty->assign('log_ok', $ok);
$smarty->assign('log_err', $err);
$smarty->display('inc_show_import_log.tpl');