<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Add mediuser's function, group or/and permission form
 */

CCanDo::checkEdit();

$group_id = CView::get("group_id", "ref class|CGroups");
$user_id  = CView::get("user_id", "ref class|CMediusers");
CView::checkin();

$group  = new CGroups();
$groups = $group->loadList();
$functions = array();

if ($group->load($group_id)) {
  $functions = $group->loadFunctions();
}

$perm_object = new CPermObject();;

$smarty = new CSmartyDP();

$smarty->assign("groups",      $groups);
$smarty->assign("functions",   $functions);
$smarty->assign("group_id",    $group_id);
$smarty->assign("user_id",     $user_id);
$smarty->assign("perm_object", $perm_object);

$smarty->display("inc_function_add");