<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Stock\CProductCategory;

CCanDo::checkEdit();

$category_id = CValue::getOrSession('category_id');

$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('category_id', $category_id);
$smarty->assign('list_categories', $list_categories);
$smarty->display('vw_return_forms.tpl');
