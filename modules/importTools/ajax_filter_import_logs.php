<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;


CCanDo::checkAdmin();

$import_mod_name   = CView::get('import_mod_name', 'str');
$import_class_name = CView::get('import_class_name', 'str');
$date_log_min      = CView::get('_date_log_min', 'str');
$date_log_max      = CView::get('_date_log_max', 'str');

$logs = array(
  'info',
  'warning',
  'error'
);

CView::checkin();

CView::enableSlave();

$smarty = new CSmartyDP();

$smarty->assign('import_mod_name', $import_mod_name);
$smarty->assign('import_class_name', $import_class_name);
$smarty->assign('date_log_min', $date_log_min);
$smarty->assign('date_log_max', $date_log_max);
$smarty->assign('log_type', $logs);

$smarty->display('inc_show_import_logs.tpl');