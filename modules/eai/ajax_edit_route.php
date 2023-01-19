<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CEAIRoute;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;

CCanDo::checkEdit();

$route_id   = CValue::get("route_id");
$actor_guid = CValue::get("actor_guid");

$list_receiver = CApp::getChildClasses(CInteropReceiver::class, true);
$list_sender   = CApp::getChildClasses(CInteropSender::class, true);

if ($actor_guid) {
  $actor = CMbObject::loadFromGuid($actor_guid);
}

$route = new CEAIRoute();
$route->load($route_id);
if (!$route->_id && isset($actor)) {
  $route->sender_class = $actor->_class;
  $route->sender_id    = $actor->_id;
}

$route->loadRefReceiver();
$route->loadRefSender();

$smarty = new CSmartyDP();
$smarty->assign("route"        , $route);
$smarty->assign("list_receiver", $list_receiver);
$smarty->assign("list_sender"  , $list_sender);
$smarty->display("inc_edit_route.tpl");