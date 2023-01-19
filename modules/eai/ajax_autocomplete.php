<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkRead();

$object_class = CView::get("object_class", "str");
$input_field  = CView::get("input_field", "str");
$value_field  = CView::get($input_field, "str");
CView::checkin();

CView::enableSlave();

$where = [
  "libelle" => "IS NOT NULL",
];

$class = new $object_class();

$matches = $class->getAutocompleteList($value_field, $where, 30);
foreach ($matches as $_match) {
  $_match->updateFormFields();
}

$template = $class->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $matches);
$smarty->assign('view_field', true);
$smarty->assign('field', 'libelle');
$smarty->assign('show_view', true);
$smarty->assign("nodebug", true);
$smarty->assign('template', $template);

$smarty->display("inc_field_autocomplete");

