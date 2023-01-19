<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$mediuser     = CMediusers::get();
$is_praticien = $mediuser->isPraticien();
$praticiens   = $mediuser->loadPraticiens(PERM_READ);

$default_chir_id = $is_praticien ? $mediuser->_id : reset($praticiens)->_id;
$use_session_praticien = CAppUI::conf("dPplanningOp COperation use_session_praticien");

$debut_stat   = CView::get("debut_stat", "date default|" . CMbDT::date("-1 year"), true);
$fin_stat     = CView::get("fin_stat", "date default|now", true);
$chir_id      = CView::get("chir_id", "ref class|CMediusers default|$default_chir_id", $use_session_praticien);
$function_id  = CView::get("function_id", "ref class|CFunctions");

CView::checkin();
CView::enableSlave();

$_function = new CFunctions();
$functions = $_function->loadSpecialites(PERM_READ);

// Compter les protocoles
CStoredObject::massLoadBackRefs($praticiens, "secondary_functions");
foreach ($praticiens as $_prat) {
  $_prat->countProtocoles(null, true);
}
foreach ($functions as $_function) {
  $_function->countProtocoles(null, true);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticiens" , $praticiens);
$smarty->assign("functions"  , $functions);
$smarty->assign("chir_id"    , $chir_id);
$smarty->assign("function_id", $function_id);
$smarty->assign("debut_stat" , $debut_stat);
$smarty->assign("fin_stat"   , $fin_stat);

$smarty->display("vw_stats_protocoles");