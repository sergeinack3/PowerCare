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
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropSender;

/**
 * Details interop receiver EAI
 */
CCanDo::checkRead();

$actor_guid  = CView::get("actor_guid", "str");

CView::checkin();

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);
if ($actor->_id) {
  $actor->loadRefGroup();
  $actor->loadRefUser();
  $actor->loadRefObjectConfigs();

  if ($actor instanceof CInteropSender) {
    $actor->countBackRefs("routes_sender");
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor" , $actor);
$smarty->display("inc_view_actor.tpl");