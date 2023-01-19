<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultationCategorie;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$user          = CMediusers::get();
$sel_cabinet   = CView::get("selCabinet", "ref class|CFunctions default|$user->function_id", true);
$sel_praticien = CView::get("selPrat", "ref class|CMediusers default|");
CView::checkin();

$user = CMediusers::get();
$user->loadRefFunction();

$droit = true;

// Chargement de la liste des cabinets
$function = new CFunctions();
$list_functions = $function->loadSpecialites(PERM_EDIT);
// Chargement de la liste des praticiens
$praticien = new CMediusers();
$list_praticiens = $praticien->loadPraticiens(PERM_EDIT, null, null, false, false);

// Verification du droit sur la fonction
if (!array_key_exists($sel_cabinet, $list_functions) && !array_key_exists($sel_praticien, $list_praticiens)) {
  $droit = false;
}

// Chargement des categories
$categorie  = new CConsultationCategorie();
$categories = array();
if ($droit && ($sel_cabinet || $sel_praticien)) {
  if ($sel_praticien) {
    $praticien->load($sel_praticien);
    $whereCategorie[] = "`praticien_id` = '$sel_praticien' OR `function_id` = '$praticien->function_id'";
  }
  else {
    $whereCategorie["function_id"] = " = '$sel_cabinet'";
  }
  $orderCategorie = "nom_categorie ASC";
  $categories = $categorie->loadList($whereCategorie, $orderCategorie);
  CStoredObject::massLoadFwdRef($categories, "function_id");
  foreach ($categories as $_categorie) {
    $_categorie->loadRefFunction();
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("droit"        , $droit);
$smarty->assign("selCabinet"   , $sel_cabinet);
$smarty->assign("selPraticien" , $sel_praticien);
$smarty->assign("categories"   , $categories);

$smarty->display("inc_vw_list_categories");
