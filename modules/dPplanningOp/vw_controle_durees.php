<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$mediuser      = CMediusers::get();
$is_praticien  = $mediuser->isPraticien();
$praticiens    = $mediuser->loadPraticiens(PERM_READ);
$chir_id       = CAppUI::conf("dPplanningOp COperation use_session_praticien")
  ? CValue::getOrSession("chir_id", $is_praticien ? $mediuser->user_id : reset($praticiens)->_id)
  : CValue::get("chir_id", $is_praticien ? $mediuser->user_id : reset($praticiens)->_id);
$function_id   = CValue::getOrSession("function_id");
$_function     = new CFunctions();
$functions     = $_function->loadSpecialites(PERM_READ);

// Compter les protocoles
CStoredObject::massLoadBackRefs($praticiens, "secondary_functions");
foreach ($praticiens as $_prat) {
  $_prat->countProtocoles(null, true);
}
foreach ($functions as $_function) {
  $_function->countProtocoles(null, true);
}

$smarty = new CSmartyDP();

$smarty->assign("praticiens" , $praticiens);
$smarty->assign("functions"  , $functions);
$smarty->assign("chir_id"    , $chir_id);
$smarty->assign("function_id", $function_id);

$smarty->display("vw_controle_durees");