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
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$keywords = CValue::get("triggered_ex_class_id_autocomplete_view");

CView::enableSlave();

CExObject::$_locales_cache_enabled = false;

$ex_class = new CExClass();

if ($keywords == "") {
  $keywords = "%";
}

$matches  = $ex_class->getAutocompleteList($keywords);
$template = $ex_class->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");
$smarty->assign('matches', $matches);
$smarty->assign('field', "triggered_ex_class_id");
$smarty->assign('view_field', "triggered_ex_class_id_autocomplete_view");
$smarty->assign('show_view', 1);
$smarty->assign('template', $template);
$smarty->assign('nodebug', true);
$smarty->display('inc_field_autocomplete.tpl');
