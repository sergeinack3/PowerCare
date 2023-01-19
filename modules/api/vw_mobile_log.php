<?php
/**
 * @package Mediboard\AppFine
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Api\CMobileLog;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$date_min = CView::get("_date_min", ["dateTime","default" => CMbDT::dateTime('-7 day')], true);
$date_max = CView::get("_date_max", ["dateTime","default" => CMbDT::dateTime('+1 day')], true);
$page     = CView::get('page'    , "num default|0");
CView::checkin();

$mobile_log = new CMobileLog();
$mobile_log->_date_min = $date_min;
$mobile_log->_date_max = $date_max;

$order = "log_datetime DESC";
$mobile_logs = $mobile_log->loadList([], $order);

$smarty = new CSmartyDP();
$smarty->assign("mobile_log" , $mobile_log);
$smarty->assign("mobile_logs", $mobile_logs);
$smarty->assign("page"       , $page);
$smarty->display("vw_mobile_log.tpl");
