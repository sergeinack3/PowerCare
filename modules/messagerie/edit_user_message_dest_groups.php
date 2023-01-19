<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Messagerie\CUserMessageDestGroup;

CCanDo::checkAdmin();

$group_id = CView::post('group_id', 'ref class|CUserMessageDestGroup');

CView::checkin();

$recipient_group = CUserMessageDestGroup::findOrNew($group_id);
if (!$recipient_group->_id) {
  $recipient_group->group_id = CGroups::get()->_id;
}
else {
  $recipient_group->loadUsersLinks();
}

$smarty = new CSmartyDP();
$smarty->assign('recipient_group', $recipient_group);
$smarty->display('inc_edit_user_message_dest_group.tpl');
