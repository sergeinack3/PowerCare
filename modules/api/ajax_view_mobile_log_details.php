<?php
/**
 * @package Mediboard\AppFine
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$mobile_log_guid  = CView::get('mobile_log_guid'    , "str");
CView::checkin();

$mobile_log = CMbObject::loadFromGuid($mobile_log_guid);
$mobile_log->input  = $mobile_log->input  ? unserialize($mobile_log->input)  : null;
$mobile_log->output = $mobile_log->output ? unserialize($mobile_log->output) : null;

$smarty = new CSmartyDP();
$smarty->assign("mobile_log", $mobile_log);
$smarty->display("inc_mobile_log_details.tpl");
