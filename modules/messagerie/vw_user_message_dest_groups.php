<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Mediboard\Messagerie\CUserMessageDestGroup;
use Ox\Mediboard\Messagerie\CUserMessageDestGroupUserLink;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$name_filter = CView::post('name_filter', 'str');
$user_filter = CView::post('user_filter', 'ref class|CMediusers');
$offset      = CView::post('offset', 'num default|0', true);
$refresh     = CView::post('refresh', 'bool default|0');

CView::checkin();

$where = [
  'user_message_dest_groups.group_id' => ' = ' . CGroups::get()->_id
];
$ljoin = [];

if ($name_filter) {
  $where['user_message_dest_groups.name'] = " LIKE '%{$name_filter}%'";
}

if ($user_filter) {
  $ljoin['user_message_dest_group_users'] = 'user_message_dest_group_users.group_id = user_message_dest_groups.user_message_dest_group_id';
  $where['user_message_dest_group_users.user_id'] = " = $user_filter";
}

$recipient_group = new CUserMessageDestGroup();
$total = $recipient_group->countList($where, null, $ljoin);
/** @var CUserMessageDestGroup[] $groups */
$recipient_groups = $recipient_group->loadList($where, 'name ASC', "$offset, 25", null, $ljoin);

$user_links = CUserMessageDestGroup::massLoadBackRefs($recipient_groups, 'dest_groups');
$users = CStoredObject::massLoadFwdRef($user_links, 'user_id');
CStoredObject::massLoadFwdRef($users, 'function_id');

foreach ($recipient_groups as $group) {
  $group->loadUsersLinks();
}

$smarty = new CSmartyDP();
$smarty->assign('recipient_groups', $recipient_groups);
$smarty->assign('total', $total);
$smarty->assign('offset', $offset);

if ($refresh) {
  $smarty->display('inc_list_user_message_dest_groups.tpl');
}
else {
  $smarty->display('vw_user_message_dest_groups.tpl');
}