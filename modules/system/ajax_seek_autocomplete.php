<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;

$object_class = CValue::get('object_class');
$field        = CValue::get('field');
$view_field   = CValue::get('view_field', $field);
$input_field  = CValue::get('input_field', $view_field);
$show_view    = CValue::get('show_view', 'false') == 'true';
$keywords     = CValue::get($input_field);
$limit        = CValue::get('limit', 30);
$where        = CValue::get('where', []);
$not_where    = CValue::get('not_where', []);
$ljoin        = CValue::get("ljoin", []);
$order        = CValue::get("order", null);
$group_by     = CValue::get("group_by", null);
$function_id  = CView::get('function_id', 'ref class|CFunctions');
$group_id     = CView::get('group_id', 'ref class|CGroups');
$options      = CView::get('options', 'str');

CView::checkin();
CView::enableSlave();

/** @var CMbObject $object */
$object = new $object_class();
$ds     = $object->getDS();

foreach ($where as $key => $value) {
    $values       = explode('|', $value);
    $where[$key]  = count($values) > 1 ? $ds->prepareIn($values) : $ds->prepare("= %", $value);
    $object->$key = $value;
}

foreach ($not_where as $key => $value) {
    $values       = explode('|', $value);
    $where[$key]  = count($values) > 1 ? $ds->prepareNotIn($values) : $ds->prepare("!= %", $value);
    $object->$key = $value;
}

if ($keywords == "") {
    $keywords = "%";
}

if ($group_id) {
    $where["group_id"] = $ds->prepare("= ?", $group_id);
} elseif ($function_id) {
    $where["function_id"] = $ds->prepare("= ?", $function_id);
}

if ($options && $options['no_show_elements']) {
    $options['no_show_elements'] = explode(",", $options['no_show_elements']);
} else {
    $options['no_show_elements'] = false;
}

$matches  = $object->getAutocompleteList($keywords, $where, $limit, $ljoin, $order, $group_by);
$template = $object->getTypedTemplate("autocomplete");

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('matches', $matches);
$smarty->assign('field', $field);
$smarty->assign('view_field', $view_field);
$smarty->assign('show_view', $show_view);
$smarty->assign('template', $template);
$smarty->assign('keywords', $keywords);
$smarty->assign('nodebug', true);
$smarty->assign("input", "");
$smarty->assign("options", $options);
$smarty->display('inc_field_autocomplete.tpl');
