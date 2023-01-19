<?php 
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportCronLogs;


CCanDo::checkAdmin();

$import_mod_name = CView::get('import_mod_name', 'str');
$import_class_name = CView::get('import_class_name', 'str');
$date_log_min = CView::get('date_log_min', 'str');
$date_log_max = CView::get('date_log_max', 'str');
$type = CView::get('type', 'enum list|error|warning|info');
$page = CView::get('start', 'num default|0');

CView::checkin();

CView::enableSlave();

$log = new CImportCronLogs();

$result = $log->getLogsByType($type, $import_mod_name, $import_class_name, $date_log_min, $date_log_max, "$page, 50");

$smarty = new CSmartyDP();
$smarty->assign('logs', $result);
$smarty->assign('type', $type);
$smarty->assign('page', $page);
$smarty->assign('import_mod_name', $import_mod_name);
$smarty->assign('import_class_name', $import_class_name);
$smarty->assign('date_log_min', $date_log_min);
$smarty->assign('date_log_max', $date_log_max);
$smarty->display('inc_vw_import_log_type.tpl');