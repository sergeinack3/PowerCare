<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$min_date = CValue::getOrSession("ua_min_date", CMbDT::date("-1 WEEK") . " 00:00:00");
$max_date = CValue::getOrSession("ua_max_date", CMbDT::date("+1 DAY") . " 00:00:00");

$smarty = new CSmartyDP();
$smarty->assign("min_date", $min_date);
$smarty->assign("max_date", $max_date);
$smarty->display("vw_user_agents.tpl");
