<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Interop\Eai\Transformations\CLinkActorSequence;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;

CCanDo::checkAdmin();

$sequence_id    = CView::get('rule_sequence_id', 'ref class|CTransformationRuleSequence');
$delete_link_id = CView::get('delete_link_id', 'ref class|CLinkActorSequence');
$receiver_guid  = CView::get('receiver_guid', 'str');
CView::checkin();

// Suppresion d'une liaison
if ($delete_link_id) {
    $link_actor_sequence = new CLinkActorSequence();
    $link_actor_sequence->load($delete_link_id);

    if ($msg = $link_actor_sequence->delete()) {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    CAppUI::stepAjax('CLinkActorSequence-msg-delete');
} else {
    $rule_sequence = new CTransformationRuleSequence();
    $rule_sequence->load($sequence_id);

    $receiver = CMbObject::loadFromGuid($receiver_guid);
    if (!$receiver || !$receiver->_id) {
        CAppUI::stepAjax('CInteropActor-msg-No actor', UI_MSG_ERROR);
    }

    $link_actor_sequence = new CLinkActorSequence();
    $link_actor_sequence->actor_id    = $receiver->_id;
    $link_actor_sequence->actor_class = $receiver->_class;
    $link_actor_sequence->sequence_id = $sequence_id;

    $link_actor_sequence->loadMatchingObject();
    if ($link_actor_sequence->_id) {
        CAppUI::stepAjax('CLinkActorSequence-msg-Link already exist', UI_MSG_ERROR);
    }

    if ($msg = $link_actor_sequence->store()) {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }

    CAppUI::stepAjax('CLinkActorSequence-msg-create');
}
