<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CEAIRoute;

CCanDo::checkEdit();

$route = new CEAIRoute();

$routes  = array();
$senders = array();

$all_routes = $route->loadList(null, "sender_id ASC");
CStoredObject::massLoadFwdRef($all_routes, "sender_id");
CStoredObject::massLoadFwdRef($all_routes, "receiver_id");

foreach ($all_routes as $_route) {
  /** @var CEAIRoute $_route */
  $sender = $_route->loadRefSender();
  $_route->loadRefReceiver();

  $senders[$sender->_guid]  = $sender;

  $routes[$sender->_guid][] = $_route;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("routes" , $routes);
$smarty->assign("senders", $senders);

$smarty->display("inc_list_route.tpl");