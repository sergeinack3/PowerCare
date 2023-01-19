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
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Eai\Transformations\CTransformation;
use Ox\Interop\Eai\Transformations\CTransformationRule;

/**
 * Edit transformaiton rule EAI
 */
CCanDo::checkAdmin();

$actor_guid    = CValue::getOrSession("actor_guid");
$event_class   = CValue::getOrSession("event_class");
$message_class = CValue::getOrSession("message_class");

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);

$event = new $event_class;

/** @var CInteropNorm $message */
$message = new $message_class;

$transformation = new CTransformation();
$transformation->actor_id    = $actor->_id;
$transformation->actor_class = $actor->_class;
$transformations = $transformation->loadMatchingList();

// On charge la liste des règles possibles en fonction des propriétés de l'évènement
$transf_rule  = new CTransformationRule();
$transf_rules = array();
if ($where = $transf_rule->bindObject($message, $event)) {
  $transf_rules = $transf_rule->loadList($where, "rank");
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("actor"         , $actor);
$smarty->assign("event"         , $event);
$smarty->assign("transf_rules"  , $transf_rules);
$smarty->assign("transformation", $transformation);

$smarty->display("inc_link_transformation_rules.tpl");
