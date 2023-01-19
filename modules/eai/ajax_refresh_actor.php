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

/**
 * Details interop receiver EAI
 */
CCanDo::checkRead();

$actor_guid  = CView::get('actor_guid', 'str');
$actor_class = CView::get('actor_class', 'str');
CView::checkin();

// Chargement de l'acteur d'interopérabilité
if ($actor_class) {
    $actor = new $actor_class();
    $actor->updateFormFields();
    $actor->loadRefGroup();
    $actor->lastMessage();
    $actor->isINSCompatible();
} elseif ($actor_guid) {
    /** @var CInteropActor $actor */
    $actor = CMbObject::loadFromGuid($actor_guid);
    if ($actor->_id) {
        $actor->loadRefGroup();
        $actor->loadRefUser();
        $actor->isReachable();
        $actor->lastMessage();
        $actor->isINSCompatible();
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("_actor", $actor);
$smarty->display("inc_actor.tpl");
