<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Configure
 */
CCanDo::checkAdmin();

$object_servers = array(
  "eai" => array(
    "CInteropActorHandler"
  ),
  "sip" => array(
    "CSipObjectHandler"
  ),
  "smp" => array(
    "CSmpObjectHandler"
  ),
  "sms" => array(
    "CSmsObjectHandler"
  ),
  "sa"  => array (
    "CSaObjectHandler",
    "CSaEventObjectHandler",
  )
);

$group = new CGroups();
$groups = $group->loadList();
foreach ($groups as $_group) {
  $_group->loadConfigValues(); 
  $_group->isIPPSupplier();
  $_group->isNDASupplier();
}


$actor_classes = [
    "sender" => array_values(CInteropSender::getChildSenders()),
    "receiver" => array_values(CInteropReceiver::getChildReceivers(true))
];

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object_servers", $object_servers);
$smarty->assign("groups"        , $groups);
$smarty->assign("actor_classes" , $actor_classes);
$smarty->display("configure.tpl");

