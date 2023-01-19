<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $g;

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Stock\CProductStockGroup;

CCanDo::checkRead();

$list_stocks = new CProductStockGroup();

$where             = array();
$where['group_id'] = " = '$g'";
$orderby           = "quantity / order_threshold_min";
$list_stocks       = $list_stocks->loadList($where, $orderby, 40);
foreach ($list_stocks as $stock) {
  $stock->loadRefOrders();
}

$colors = array('#F00', '#FC3', '#1D6', '#06F', '#000');

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('list_stocks', $list_stocks);
$smarty->assign('colors', $colors);

$smarty->display('vw_idx_report.tpl');

