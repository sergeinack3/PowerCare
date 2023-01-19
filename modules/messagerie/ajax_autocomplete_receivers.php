<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessageDest;

CCanDo::checkRead();

$keywords = CView::get('keywords', 'str');

CView::checkin();

$connected_users = CUserMessageDest::getConnectedUsers();

$group = CGroups::loadCurrent();
$user = CMediusers::get();

$ljoin = array(
  'users' => 'users.user_id = users_mediboard.user_id'
);
$where = array(
  'users_mediboard.actif' => " = '1'",
  "users.user_username LIKE '{$keywords}%' OR users.user_first_name LIKE '{$keywords}%'" .
  " OR users.user_last_name LIKE '{$keywords}%' OR users.user_username LIKE '{$keywords}%'",
);

switch (CAppUI::gconf('messagerie messagerie_interne resctriction_level_messages')) {
  case 'group':
    $ljoin['functions_mediboard'] = 'functions_mediboard.function_id = users_mediboard.function_id';
    $where['functions_mediboard.group_id'] = " = '{$group->_id}'";
    break;
  case 'function':
    $where['users_mediboard.function_id'] = " = '{$user->function_id}'";
    break;
  default:
}

$users = $user->loadList($where, 'users.user_last_name, users.user_first_name', 30, 'users_mediboard.user_id', $ljoin);
CMbObject::massLoadFwdRef($users, 'function_id');
foreach ($users as $user) {
  $user->loadRefFunction();
  
  if (in_array($user->_id, $connected_users)) {
    $user->_is_connected = true;
  }
}

$smarty = new CSmartyDP();
$smarty->assign('users', $users);
$smarty->display('inc_receivers_autocomplete.tpl');