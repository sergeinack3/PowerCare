<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

CView::checkin();

// users
$user  = new CMediusers();
$users = $user->loadUsers(PERM_EDIT);

// functions
$function  = new CFunctions();
$functions = $function->loadListWithPerms(PERM_EDIT);

// smarty
$smarty = new CSmartyDP();
$smarty->assign("users", $users);
$smarty->assign("functions", $functions);
$smarty->display("vw_categories");