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
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\Transformations\CLinkActorSequence;
use Ox\Interop\Eai\Transformations\CTransformationRule;

/**
 * Formats available
 */
CCanDo::checkRead();

$actor_guid = CView::get("actor_guid", "str");

CView::checkin();

/** @var CInteropActor $actor */
$actor = CMbObject::loadFromGuid($actor_guid);
$rule_sequences_link = $actor->loadRefsEAITransformation();
CStoredObject::massLoadFwdRef($rule_sequences_link, "sequence_id");

$transformations = [];
/** @var CLinkActorSequence $_rule_sequence_link */
foreach ($rule_sequences_link as $_rule_sequence_link) {
    $sequence = $_rule_sequence_link->loadRefSequence();
    /** @var CTransformationRule $_rule */
    foreach ($sequence->loadRefsTransformationRules() as $_rule) {
        $_rule->_ref_transformation_rule_sequence = $sequence;
        $transformations[] = $_rule;
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("actor", $actor);
$smarty->assign("transformations", $transformations);
$smarty->assign("readonly", true);
$smarty->display("CInteropActor_eai_transformations.tpl");
