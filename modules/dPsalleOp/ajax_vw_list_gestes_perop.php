<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkEdit();
$filtre              = new CGestePerop();
$filtre->user_id     = CView::get("user_id", "ref class|CMediusers");
$filtre->function_id = CView::get("function_id", "ref class|CFunctions");
$page                = CView::get("page", "num default|0");
$keywords            = CView::get("keywords", "str", true);
CView::checkin();

$geste_perop  = new CGestePerop();
$order        = "libelle ASC";
$limit        = "$page, 30";
$gestes_perop = $geste_perop->loadList(null, $order, $limit);
$nbResultat   = $geste_perop->countList();

CStoredObject::massLoadFwdRef($gestes_perop, "group_id");
CStoredObject::massLoadFwdRef($gestes_perop, "function_id");
CStoredObject::massLoadFwdRef($gestes_perop, "user_id");
CStoredObject::massLoadFwdRef($gestes_perop, "categorie_id");

foreach ($gestes_perop as $_geste) {
  $_geste->loadRefGroup();
  $_geste->loadRefFunction();
  $_geste->loadRefUser()->loadRefFunction();
  $_geste->loadRefCategory();
  $_geste->loadRefFile();
}

$filtre->loadRefUser();
$filtre->loadRefFunction();

$evenement_category   = new CAnesthPeropCategorie();
$evenement_categories = $evenement_category->loadGroupList(null, "libelle ASC");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("geste_perop"         , $geste_perop);
$smarty->assign("gestes_perop"        , $gestes_perop);
$smarty->assign("nbResultat"          , $nbResultat);
$smarty->assign("page"                , $page);
$smarty->assign("filtre"              , $filtre);
$smarty->assign("keywords"            , $keywords);
$smarty->assign("evenement_categories", $evenement_categories);
$smarty->display("inc_vw_list_gestes_perop");
