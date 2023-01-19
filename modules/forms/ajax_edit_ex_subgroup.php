<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassFieldSubgroup;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$ex_subgroup_id = CValue::get("ex_subgroup_id");
$ex_group_id    = CValue::get("ex_group_id");

CExObject::$_locales_cache_enabled = false;
$ex_subgroup                       = new CExClassFieldSubgroup();

if ($ex_subgroup->load($ex_subgroup_id)) {
  $ex_subgroup->loadRefsNotes();
}
else {
  $ex_subgroup->parent_id    = $ex_group_id;
  $ex_subgroup->parent_class = "CExClassFieldGroup";
}

$ex_subgroup->loadRefPredicate()->loadView();
$ex_subgroup->loadRefProperties();
$ex_subgroup->loadRefsChildrenGroups();

$ex_group = $ex_subgroup->getExGroup();
$ex_group->loadRefExClass();

$smarty = new CSmartyDP();
$smarty->assign("ex_subgroup", $ex_subgroup);
$smarty->assign("ex_group", $ex_group);
$smarty->display("inc_edit_ex_subgroup.tpl");