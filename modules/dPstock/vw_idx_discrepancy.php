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
use Ox\Mediboard\Stock\CProductStockGroup;

CCanDo::checkEdit();

$service_id  = CValue::getOrSession('service_id');
$category_id = CValue::getOrSession('category_id');

// Services list
$list_services = CProductStockGroup::getServicesList();

// Loads the required Category and the complete list
$category        = new CProductCategory();
$list_categories = $category->loadList(null, 'name');

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('service_id', $service_id);
$smarty->assign('list_services', $list_services);

$smarty->assign('category_id', $category_id);
$smarty->assign('list_categories', $list_categories);

$smarty->display('vw_idx_discrepancy.tpl');

