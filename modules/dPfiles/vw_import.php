<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

$regex      = CView::post('regex', 'str', true);
$regex_date = CView::post('regex_date', 'str', true);

CView::checkin();

$regex      = stripcslashes($regex);
$regex_date = stripcslashes($regex_date);

$user  = new CMediusers();
$users = $user->loadListWithPerms(PERM_EDIT);

$me              = CMediusers::get();
$users[$me->_id] = $me;

uasort(
  $users,
  function ($a, $b) {
    return strcmp($a->_view, $b->_view);
  }
);

$smarty = new CSmartyDP();
$smarty->assign('regex', $regex);
$smarty->assign('regex_date', $regex_date);
$smarty->assign('users', $users);
$smarty->display('vw_import.tpl');