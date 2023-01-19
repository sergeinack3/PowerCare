<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CUserAgent;

CCanDo::checkAdmin();

$user_agent_id = CValue::get("user_agent_id");

$ua = new CUserAgent();
$ua->load($user_agent_id);
$ua->loadRefsNotes();

$ua->countBackRefs("user_authentications");

$detect = CUserAgent::detect($ua->user_agent_string);

$smarty = new CSmartyDP();
$smarty->assign("ua", $ua);
$smarty->assign("detect", $detect);
$smarty->display("inc_edit_user_agent.tpl");
