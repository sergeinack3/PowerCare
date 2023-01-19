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
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;

global $dialog;
if ($dialog) {
  CCanDo::checkRead();
}
else if (CAppUI::gconf("dPplanningOp CSejour tab_protocole_DHE_only_for_admin")) {
  CCanDo::checkAdmin();
}
else {
  CCanDo::checkEdit();
}

$singleType = CValue::get("singleType");

// L'utilisateur est-il chirurgien ?
$mediuser      = CMediusers::get();
$is_praticien  = $mediuser->isPraticien();
$listPrat      = $mediuser->loadPraticiens(PERM_READ);
$chir_id       = CAppUI::conf("dPplanningOp COperation use_session_praticien")
  ? CValue::getOrSession("chir_id", $is_praticien ? $mediuser->user_id : reset($listPrat)->_id)
  : CValue::get("chir_id", $is_praticien ? $mediuser->user_id : reset($listPrat)->_id);
$function_id   = CValue::getOrSession("function_id");
$_function     = new CFunctions();
$listFunc      = $_function->loadSpecialites(PERM_READ);
$type          = CValue::getOrSession("type", "interv");
$sejour_type   = CValue::get("sejour_type");
$page          = CValue::get("page", array(
    "sejour" => 0,
    "interv" => 0)
);

$idex_selector = CView::get("idex_selector", "bool default|1");

CView::checkin();

//Limite de la recherche des protocoles de DHE à l'établissement courant
list($ljoinSecondary, $whereSecondary) = CProtocole::checkMultiEtab();
// Protocoles disponibles
CStoredObject::massLoadBackRefs($listPrat, "secondary_functions", null, $whereSecondary, $ljoinSecondary);
foreach ($listPrat as $_prat) {
  $_prat->countProtocoles($sejour_type, false, 1);
}
foreach ($listFunc as $_function) {
  $_function->countProtocoles($sejour_type, false, 1);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("singleType"  , $singleType);
$smarty->assign("page"        , $page);
$smarty->assign("listPrat"    , $listPrat);
$smarty->assign("listFunc"    , $listFunc);
$smarty->assign("chir_id"     , $chir_id);
$smarty->assign("mediuser"    , $mediuser);
$smarty->assign("sejour_type" , $sejour_type);
$smarty->assign("function_id" , $function_id);
$smarty->assign("idex_selector" , $idex_selector);

$smarty->display("vw_protocoles");
