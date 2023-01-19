<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCando;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Interop\Eai\Transformations\CLinkActorSequence;

CCanDo::checkAdmin();

$sequence_id = CView::get('rule_sequence_id', 'ref class|CTransformationRuleSequence');
CView::checkin();

$link_actor_sequence = new CLinkActorSequence();
$link_actor_sequence->sequence_id = $sequence_id;
$link_actors = $link_actor_sequence->loadMatchingList();

/** @var CLinkActorSequence $_link_actor */
foreach ($link_actors as $_link_actor) {
    $_link_actor->loadRefActor();
}

$smarty = new CSmartyDP();
$smarty->assign('link_actors', $link_actors);
$smarty->assign('sequence_id', $sequence_id);
$smarty->display('inc_list_actors');
