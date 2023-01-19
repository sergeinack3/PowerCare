<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$string  = CValue::session("string");
$mode    = CValue::session("mode");
$compare = CValue::session("compare");

$smarty = new CSmartyDP();
$smarty->assign("string", $string);
$smarty->assign("mode", $mode);
$smarty->assign("compare", $compare);
$smarty->display("charset_toolbox.tpl");