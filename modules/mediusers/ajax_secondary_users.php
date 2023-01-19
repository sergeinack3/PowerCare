<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$main_user_id = CView::get('main_user_id', 'ref class|CMediusers notNull');
$action = CView::get('action', 'enum list|display|create|search default|display');
$refresh = CView::get('refresh', 'bool default|0');
$filter = CView::get('filter', 'str');
$function_id = CView::get('function_id', 'ref class|CFunctions');

CView::checkin();

$main_user = CMediusers::get($main_user_id);
$main_user->loadRefFunction();

$smarty = new CSmartyDP();
$smarty->assign('main_user', $main_user);
$smarty->assign('action', $action);

switch ($action) {
  case 'create':
    $user = new CMediusers();
    $smarty->assign('user', $user);
    $template = 'inc_create_descendant_users.tpl';
    break;
  case 'search':
    $ds = CSQLDataSource::get('std');
    $ljoin = array(
      'users'               => 'users.user_id = users_mediboard.user_id',
      'functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.function_id'
    );
    $where = array(
      'functions_mediboard.group_id'  => " = {$main_user->_ref_function->group_id}",
      'users_mediboard.user_id'       => " != {$main_user->_id}"
    );

    if ($function_id) {
      $where['users_mediboard.function_id'] = " = '$function_id'";
    }
    if ($filter) {
      $like = $ds->prepareLike("%$filter%");
      $where[] = "(user_username {$like} OR user_first_name {$like} OR user_last_name {$like})";
    }

    $order = 'users.user_last_name ASC, users.user_first_name ASC';

    $users = $main_user->loadList($where, $order, null, 'users_mediboard.user_id', $ljoin);

    CMbObject::massLoadFwdRef($users, 'function_id');
    CMbObject::massLoadBackRefs($users, 'secondary_users');
    foreach ($users as $user) {
      $user->loadRefFunction();
      $user->loadRefsSecondaryUsers();
    }

    $smarty->assign('users', $users);
    $template = 'inc_search_secondary_users.tpl';
    break;
  default:
    $template = 'inc_secondary_users.tpl';
}

$smarty->display($template);