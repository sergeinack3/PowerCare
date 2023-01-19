<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductMovement;

CCanDo::check();

$code = CView::get("code", "str");

CView::checkin();

$codes = CProductMovement::$accountingcodes;

$chars = str_split($code);

$tree = array();

$partial_code = "";
foreach ($chars as $_char) {
  $partial_code .= $_char;

  if (isset($codes[$partial_code])) {
    $tree[] = $partial_code;
  }
}

$smarty = new CSmartyDP();

$smarty->assign('code', $code);
$smarty->assign('codes', $codes);
$smarty->assign('tree', $tree);

$smarty->display('inc_vw_accounting_code.tpl');
