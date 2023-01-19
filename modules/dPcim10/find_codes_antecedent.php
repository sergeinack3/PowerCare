<?php
/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\CCodeCIM10;

CCanDo::checkRead();
$mater    = CView::get("mater", "str");
$callback = CView::get("callback", "str");
CView::checkin();

$version = CCodeCIM10::getVersion();

$code = $mater ? "O74" : "T88";
$cim10 = CCodeCIM10::get($code, CCodeCIM10::FULL);

$smarty = new CSmartyDP();

$smarty->assign("cim10"   , $cim10);
$smarty->assign('version' , $version);
$smarty->assign('callback', $callback);
$smarty->display("find_codes_antecedent");
