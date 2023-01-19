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
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Printing\CPrinter;
use Ox\Mediboard\Printing\CSourceLPR;
use Ox\Mediboard\Printing\CSourceSMB;

CCanDo::checkRead();

$printer_id = CView::get("printer_id", "num default|0", true);

CView::checkin();

$printer = new CPrinter();
$printer->load($printer_id);

if ($printer->_id) {
  $printer->loadTargetObject();
}

$source  = new CSourceLPR();
$sources = $source->loadlist();

$source  = new CSourceSMB();
$sources = array_merge($sources, $source->loadlist());

$function  = new CFunctions();
$where     = array(
  "group_id" => "= '" . CGroups::loadCurrent()->_id . "'"
);
$functions = $function->loadListWithPerms(PERM_READ, $where, "text");

$smarty = new CSmartyDP();

$smarty->assign("printer", $printer);
$smarty->assign("sources", $sources);
$smarty->assign("functions", $functions);

$smarty->display("inc_edit_printer.tpl");

