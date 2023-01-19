<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CEAIRoute;
use Ox\Interop\Eai\CInteropSender;

CCanDo::checkEdit();

$actor_guid = CValue::get("actor_guid");

/** @var CInteropSender $actor */
$sender = CMbObject::loadFromGuid($actor_guid);

$route               = new CEAIRoute();
$route->sender_class = $sender->_class;
$route->sender_id    = $sender->_id;
$routes = $route->loadMatchingList();

foreach ($routes as $_route) {
  /** @var CEAIRoute $_route */
  $_route->loadRefReceiver();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sender", $sender);
$smarty->assign("routes", $routes);

$smarty->display("inc_list_actor_routes.tpl");