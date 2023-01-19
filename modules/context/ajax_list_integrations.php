<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Context\CContextualIntegration;

CCanDo::checkRead();

$integration = new CContextualIntegration();
$list        = $integration->loadGroupList(null, "title");

$smarty = new CSmartyDP();
$smarty->assign("list_integrations", $list);
$smarty->display("inc_list_integrations.tpl");