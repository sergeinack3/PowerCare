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
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActor;

/**
 * View interop receiver EAI
 */
CCanDo::checkRead();

$actor_class = CValue::get("actor_class");

/** @var CInteropActor $actor */
$actor = new $actor_class;
$actors = $actor->countObjects();

$count_actors       = 0;
$count_actors_actif = 0;

foreach ($actors as $_actor) {
    $count_actors += CMbArray::get($_actor, 'total');
    $count_actors_actif += CMbArray::get($_actor, 'total_actif');
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("parent_class"      , $actor_class);
$smarty->assign("actor"             , $actor);
$smarty->assign("actors"            , $actors);
$smarty->assign("count_actors"      , $count_actors);
$smarty->assign("count_actors_actif", $count_actors_actif);
$smarty->display("inc_actors.tpl");

