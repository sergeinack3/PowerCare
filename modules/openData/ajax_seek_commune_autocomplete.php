<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;

$object_class = CValue::get('object_class');
$field        = CValue::get('field');
$view_field   = CValue::get('view_field', $field);
$input_field  = CValue::get('input_field', $view_field);
$show_view    = CValue::get('show_view', 'false') == 'true';
$keywords     = CValue::get($input_field);
$limit        = CValue::get('limit', 30);

CView::enableSlave();

/** @var CMbObject $object */
$object = new $object_class;
$ds     = $object->getDS();


if ($keywords == "") {
  $keywords = "%";
}

$matches = $object->getAutocompleteList($keywords, null, $limit);

$template = $object->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP();
$smarty->assign('matches', $matches);
$smarty->assign('field', $field);
$smarty->assign('view_field', $view_field);
$smarty->assign('show_view', $show_view);
$smarty->assign('template', $template);
$smarty->assign('nodebug', true);
$smarty->assign("input", "");
$smarty->display('inc_field_commune_autocomplete');
