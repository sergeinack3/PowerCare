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

CCanDo::checkEdit();

$start = intval(CValue::get("start", 0));

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("start", $start);
$smarty->display('vw_idx_reception.tpl');
