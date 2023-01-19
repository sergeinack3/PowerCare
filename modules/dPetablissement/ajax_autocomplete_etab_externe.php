<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CEtabExterne;

$field       = CValue::get('field');
$view_field  = CValue::get('view_field', $field);
$input_field = CValue::get('input_field', $view_field);
$show_view   = CValue::get('show_view', 'false') == 'true';
$keywords    = CValue::get($input_field);

CView::enableSlave();

$etab_externe = new CEtabExterne();
$where = array();
if ($keywords) {
  $where['nom'] = " LIKE '%$keywords%'";
}
$etab_externes = $etab_externe->loadList($where, '`priority` DESC, `nom` ASC', '50');

$template = $etab_externe->getTypedTemplate("autocomplete");

$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches"   , $etab_externes);
$smarty->assign("field"     , $field);
$smarty->assign('view_field', $view_field);
$smarty->assign('show_view' , $show_view);
$smarty->assign("template"  , $template);
$smarty->assign('input'     , "");
$smarty->assign('nodebug'   , true);

$smarty->display("inc_field_autocomplete");