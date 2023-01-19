<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkEdit();

$tabs = array(
  'vw_idx_societe',
  'vw_idx_stock_location',
  'vw_idx_selection',
  'vw_idx_endowment',
  'vw_idx_category',
);

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("tabs", $tabs);
$smarty->display('vw_idx_setup.tpl');

