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
use Ox\Interop\Eai\CInteropActor;

CCanDo::checkEdit();

$actor_guid  = CValue::getOrSession("actor_guid");
$actor_class = CValue::getOrSession("actor_class");

// Chargement de l'acteur d'interopérabilité
if ($actor_class) {
  $actor = new $actor_class;
  $actor->updateFormFields();
}
else {
  if ($actor_guid) {
    /** @var CInteropActor $actor */
    $actor = CMbObject::loadFromGuid($actor_guid);
    if ($actor->_id) {
      $actor->loadRefGroup();
      $actor->loadRefUser();
    }
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor" , $actor);
$smarty->display("inc_edit_actor.tpl");