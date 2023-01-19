<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$user_connected = CMediusers::get();
$user_id = CView::get("user_id", 'ref class|CMediusers');
if (!$user_id) {
  $user_id = $user_connected->_id;
}
$account_id = CView::get("account_id", 'num', true);

CView::checkin();

//user
$user = new CMediusers();
$user->load($user_id);

//CSourcePOP account
$account = new CSourcePOP();

//getting the list of user with the good rights
$listUsers = $user->loadListWithPerms(PERM_EDIT);
$where = array();
$where["source_pop.is_private"]   = "= '0'";
$where["source_pop.object_class"] = "= 'CMediusers'";
$where["users_mediboard.function_id"] = "= '$user->function_id'";
$where["users_mediboard.user_id"] = CSQLDataSource::prepareIn(array_keys($listUsers));
$ljoin = array();
$ljoin["users_mediboard"] = "source_pop.object_id = users_mediboard.user_id AND source_pop.object_class = 'CMediusers'";

//all accounts linked to a mediuser
//all accounts from an unique mediuser are grouped, in order to have the mediusers list
/** @var CSourcePOP[] $accounts_available */
$accounts_available = $account->loadList($where, null, null, null, $ljoin);

//getting user list
$users = array();
foreach ($accounts_available as $_account) {
  $userPop = $_account->loadRefMetaObject();
  $users[$userPop->_id] = $userPop;
}

//all accounts to the selected user
$where["source_pop.object_id"] = " = '$user->_id'";

//if user connected, show the private source pop
if ($user_id == $user_connected->_id) {
  $where["source_pop.is_private"] = " IS NOT NULL";
}

$accounts_user = $account->loadList($where, null, null, null, $ljoin);

//if no account_id, selecting the first one
if (!$account_id && count($accounts_available)) {
  $account_temp = reset($accounts_available);
  $account_id = $account_temp->_id;
}


//switching account check, if session account_id not in user_account, reset account_id
if (!array_key_exists($account_id, $accounts_user)) {
  if (count($accounts_user)) {
    $account_temp = reset($accounts_user);
    $account_id = $account_temp->_id;
  }
  else {
    $account_id = null;
  }
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("user",  $user);
$smarty->assign("users", $users);
$smarty->assign("mails", $accounts_user);
$smarty->assign("account_id", $account_id);
$smarty->display("vw_list_externalMessages.tpl");