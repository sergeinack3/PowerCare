<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$user = CMediusers::get();

$sel_cabinet   = CView::get("selCabinet", "ref class|CFunctions default|$user->function_id", true);
$sel_praticien = CView::get("selPrat", "ref class|CMediusers default|");

CView::checkin();

// Chargement de la liste des cabinets
$function = new CFunctions();
$list_functions = $function->loadSpecialites(PERM_EDIT);

// Chargement de la liste des praticiens
$praticien = new CMediusers();
$list_praticiens = $praticien->loadPraticiens(PERM_EDIT, null, null, false, false);

// Verification du droit sur la fonction
if (!array_key_exists($sel_cabinet, $list_functions)) {
  $sel_cabinet = $user->function_id;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listFunctions"    , $list_functions);
$smarty->assign("selCabinet"       , $sel_cabinet);
$smarty->assign("listPraticiens", $list_praticiens);
$smarty->assign("selPraticien"     , $sel_praticien);

$smarty->display("vw_categories");
