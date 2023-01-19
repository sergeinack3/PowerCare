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
use Ox\Core\CView;
use Ox\Mediboard\System\CUserAgent;

CCanDo::checkRead();

$user_agent_id = CValue::get("user_agent_id");

CView::enforceSlave();

$ua   = new CUserAgent();
if ($user_agent_id) {
  $ua->load($user_agent_id);

  if ($ua->_id) {
    $ua->countBackRefs("user_authentications");
  }
}

$smarty = new CSmartyDP();
$smarty->assign("_user_agent", $ua);
$smarty->display("inc_vw_user_agents_line.tpl");