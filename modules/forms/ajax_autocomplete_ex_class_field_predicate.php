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
use Ox\Mediboard\System\Forms\CExClassFieldPredicate;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$ex_class_id       = CView::get("ex_class_id", "num");
$ex_class_field_id = CView::get("ex_class_field_id", "num");
$keywords          = CView::get("predicate_id_autocomplete_view", "str");

CView::checkin();
CView::enableSlave();

CExObject::$_locales_cache_enabled = false;

$where = array(
  "ex_class_field_group.ex_class_id" => "= '$ex_class_id'",
);
$ljoin = array(
  "ex_class_field"       => "ex_class_field.ex_class_field_id             = ex_class_field_predicate.ex_class_field_id",
  "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
);

// Exclude current field
if ($ex_class_field_id) {
  $where["ex_class_field_predicate.ex_class_field_id"] = "!= '$ex_class_field_id'";
}

$predicate = new CExClassFieldPredicate();

if ($keywords == "") {
  $keywords = "%";
}

$matches  = $predicate->getAutocompleteList($keywords, $where, 200, $ljoin);
$template = $predicate->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");
$smarty->assign('matches', $matches);
$smarty->assign('field', "ex_class_id");
$smarty->assign('view_field', "predicate_id_autocomplete_view");
$smarty->assign('show_view', 1);
$smarty->assign('template', $template);
$smarty->assign('nodebug', true);
$smarty->display('inc_field_autocomplete.tpl');
