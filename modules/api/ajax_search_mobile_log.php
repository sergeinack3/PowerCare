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

$step  = 25;
$order = "log_datetime DESC";

$mobile_log = new CMobileLog();
/** @var CMobileLog[] $mobile_logs */
$mobile_logs = $mobile_log->loadList(null, $order, "$page, $step");
$total  = $mobile_log->countList(null);

foreach ($mobile_logs as $_mobile_log) {
  $_mobile_log->loadRefsNotes();
}

$smarty = new CSmartyDP();
$smarty->assign("page"  , $page);
$smarty->assign("step"  , $step);
$smarty->assign("total" , $total);
$smarty->assign("mobile_logs", $mobile_logs);
$smarty->display("inc_search_mobile_log.tpl");
