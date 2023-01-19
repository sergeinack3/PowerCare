<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\System\CUserAgent;
use Ox\Mediboard\System\CUserAgentGraph;
use Ox\Mediboard\System\CUserAuthentication;

CCanDo::checkRead();

$start = CValue::get("start", 0);

$min_date = CValue::get("_min_date");
$max_date = CValue::get("_max_date");

CValue::setSession("ua_min_date", $min_date);
CValue::setSession("ua_max_date", $max_date);

CView::enforceSlave();

$auth = new CUserAuthentication();
$ua   = new CUserAgent();
$ds   = $ua->getDS();

$ljoin                        = array();
$ljoin["user_authentication"] = "`user_authentication`.`user_agent_id` = `user_agent`.`user_agent_id`";

$where = array();
if ($min_date) {
  $where[] = $ds->prepare("`datetime_login` >= %", $min_date);
}

if ($max_date) {
  $where[] = $ds->prepare("`datetime_login` <= %", $max_date);
}

$where["platform_name"] = $ds->prepare("!= 'unknown'");

$total = $ua->countListGroupBy($where, null, "`user_agent`.`user_agent_id`", $ljoin);

$browsers    = $ua->countMultipleList($where, null, "browser_name", $ljoin, "browser_name");
$versions    = $ua->countMultipleList($where, null, "browser_name, browser_version", $ljoin, "browser_name, browser_version");
$platforms   = $ua->countMultipleList($where, null, "platform_name", $ljoin, "platform_name");
$devices     = $ua->countMultipleList($where, null, "device_type", $ljoin, "device_type");
$screens     = $ua->countMultipleList($where, null, "screen_width", $ljoin, "screen_width");
$methods     = $ua->countMultipleList($where, null, "pointing_method", $ljoin, "pointing_method");
//$connections = $auth->countMultipleList($where, null, "DATE_FORMAT(`datetime_login`, '%Y-%m-%d')", null, "DATE_FORMAT(`datetime_login`, '%Y-%m-%d') as datetime_login");

$graphs   = array();
$graphs[] = CUserAgentGraph::getBrowserNameSeries($browsers);
$graphs[] = CUserAgentGraph::getPlatformNameSeries($platforms);
$graphs[] = CUserAgentGraph::getDeviceTypeSeries($devices);
$graphs[] = CUserAgentGraph::getScreenSizeSeries($screens);
$graphs[] = CUserAgentGraph::getPointingMethodSeries($methods);
$graphs[] = CUserAgentGraph::getBrowserVersionSeries($versions);
//$graphs[] = CUserAgentGraph::getNbConnectionsSeries($connections);

// To get them in the list
unset($where["platform_name"]);

$uas = $ua->loadList($where, "browser_name, browser_version", "$start, 50", "`user_agent`.`user_agent_id`", $ljoin);
CStoredObject::massCountBackRefs($uas, "user_authentications");

$smarty = new CSmartyDP();
$smarty->assign("graphs", $graphs);
$smarty->assign("user_agents", $uas);
$smarty->assign("total", $total);
$smarty->assign("start", $start);
$smarty->display("inc_vw_user_agents.tpl");
