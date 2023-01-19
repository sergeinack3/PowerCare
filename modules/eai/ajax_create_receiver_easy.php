<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$actor_class = "CInteropReceiver";

/** @var CInteropReceiver $actor */
$actor = new $actor_class;
$actors = $actor->getChildReceivers();

$actor->group_id = CGroups::loadCurrent()->_id;
$actor->actif = "1";
$actor->role = CAppUI::conf("instance_role");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor" , $actor);
$smarty->assign("actors", $actors);
$smarty->assign("tabs_menu", "receiver_type");
$smarty->display("inc_create_receiver_easy.tpl");
