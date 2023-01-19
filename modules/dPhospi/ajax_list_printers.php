<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Printing\CPrinter;

CCanDo::checkRead();

$printer_id = CView::get("printer_id", "num default|0", true);

CView::checkin();

$printer = new CPrinter();
$ljoin   = array(
  "functions_mediboard" => "functions_mediboard.function_id = printer.function_id"
);
$where   = array(
  "functions_mediboard.group_id" => "= '" . CGroups::loadCurrent()->_id . "'"
);

$printers = $printer->loadList($where, "object_id, text", null, null, $ljoin);

foreach ($printers as $_printer) {
  $_printer->loadTargetObject();
  $_printer->loadRefFunction();
}

$smarty = new CSmartyDP();

$smarty->assign("printers", $printers);
$smarty->assign("printer_id", $printer_id);

$smarty->display("inc_list_printers.tpl");
