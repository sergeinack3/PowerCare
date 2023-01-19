<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

$field       = CValue::get('field');
$view_field  = CValue::get('view_field', $field);
$input_field = CValue::get('input_field', $view_field);
$show_view   = CValue::get('show_view', 'false') == 'true';
$keywords    = CValue::get($input_field);
$edit        = CValue::get('edit', 0);

CView::enableSlave();

$groups = CGroups::loadGroups($edit ? PERM_EDIT : PERM_READ);

if ($keywords) {
  foreach ($groups as $_group) {
    if (!preg_match("/^$keywords/i", $_group->text)) {
      unset($groups[$_group->_id]);
    }
  }
}

$group = new CGroups();
$template = $group->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches"   , $groups);
$smarty->assign("field"     , $field);
$smarty->assign('view_field', $view_field);
$smarty->assign('show_view' , $show_view);
$smarty->assign("template"  , $template);
$smarty->assign('input'     , "");
$smarty->assign('nodebug'   , true);

$smarty->display("inc_field_autocomplete");
