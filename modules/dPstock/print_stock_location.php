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
use Ox\Mediboard\Stock\CProductStockLocation;

CCanDo::checkRead();

$stock_location_id = CValue::get('stock_location_id');
$empty             = CValue::get('empty');

$stock_location = new CProductStockLocation();
$stock_location->load($stock_location_id);
$stock_location->loadRefsStocks();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('stock_location', $stock_location);
$smarty->assign('empty', $empty);
$smarty->display('print_stock_location.tpl');

