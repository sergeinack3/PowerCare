<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassFieldActionButton;

CCanDo::checkEdit();

$ex_class_id               = CView::get("ex_class_id", "ref class|CExClass notNull");
$keywords                  = CView::get("_ex_field_view", "str");
$exclude_ex_field_id       = CView::get("exclude_ex_field_id", "ref class|CExClassField");
$compat_target_ex_field_id = CView::get("compat_target_ex_field_id", "ref class|CExClassField");
$compat_source_ex_field_id = CView::get("compat_source_ex_field_id", "ref class|CExClassField");

CView::checkin();

CView::enableSlave();

$ex_class = new CExClass();
$ex_class->load($ex_class_id);

$where = array(
  "ex_class_field_group.ex_class_id" => "= '$ex_class_id'",
);

$ljoin = array(
  "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
);

if ($exclude_ex_field_id) {
  $where["ex_class_field.ex_class_field_id"] = "!= '$exclude_ex_field_id'";
}

$ex_field = new CExClassField();

$ds = $ex_field->getDS();

$compatTypes = null;

if ($compat_target_ex_field_id) {
  $ex_field->load($compat_target_ex_field_id);
  $prop = $ex_field->prop;

  list($propType) = explode(" ", $prop, 2);

  $compatTypes = array();
  foreach (CExClassFieldActionButton::$compat as $_propType => $_types) {
    if (in_array($propType, $_types)) {
      $compatTypes[] = $_propType;
    }
  }
}
elseif ($compat_source_ex_field_id) {
  $ex_field->load($compat_source_ex_field_id);
  $prop = $ex_field->prop;

  list($propType) = explode(" ", $prop, 2);

  $types = CExClassFieldActionButton::$compat[$propType];
}

if ($compatTypes !== null) {
  $where[] = "prop " . $ds->prepareIn($compatTypes) . " OR SUBSTRING_INDEX(prop, ' ', 1) " . $ds->prepareIn($compatTypes);
}

if (empty($keywords)) {
  $keywords = "%";
}

$matches  = $ex_field->getAutocompleteList($keywords, $where, 200, $ljoin);
$template = $ex_field->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");
$smarty->assign('matches', $matches);
$smarty->assign('field', "ex_class_id");
$smarty->assign('view_field', "_ex_field_view");
$smarty->assign('show_view', 1);
$smarty->assign('template', $template);
$smarty->assign('nodebug', true);
$smarty->display('inc_field_autocomplete.tpl');
