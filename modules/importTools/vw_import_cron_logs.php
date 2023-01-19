<?php 
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportCronLogs;

CCanDo::checkAdmin();

CView::checkin();

$import_log = new CImportCronLogs();

$smarty = new CSmartyDP();
$smarty->assign('import_log', $import_log);
$smarty->assign('date_log', CMbDT::dateTime('-1 WEEK'));
$smarty->display('vw_import_cron_logs.tpl');