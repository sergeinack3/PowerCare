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
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropNorm;

/**
 * Link transformation rule
 */
CCanDo::checkAdmin();

$event_class   = CValue::getOrSession("event_class");
$message_class = CValue::getOrSession("message_class");
$actor_guid    = CValue::getOrSession("actor_guid");

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);

/** @var CInteropNorm $message */
$message = new $message_class;

$event = null;
$where = array();

if ($event_class) {
  $event = new $event_class;

  $where[] = "message IS NULL OR message = '$event_class'";
}

$transformations = $actor->loadRefsEAITransformation($where);
foreach ($transformations as $_transformation) {
  $_transformation->loadRefEAITransformationRule();
}

CStoredObject::massCountBackRefs($transformations, "notes");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("actor"          , $actor);
$smarty->assign("event"          , $event);
$smarty->assign("message"        , $message);
$smarty->assign("transformations", $transformations);
$smarty->assign("readonly"       , false);

$smarty->display("inc_list_transformations.tpl");