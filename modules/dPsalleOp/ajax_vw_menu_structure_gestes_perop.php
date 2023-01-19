<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CAnesthPeropChapitre;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkRead();
$operation_id   = CView::get("operation_id", "ref class|COperation");
$datetime       = CView::get("datetime", "dateTime");
$see_all_gestes = CView::get("see_all_gestes", "bool default|0", true);
$type           = CView::get("type", "str default|perop");
CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$group = CGroups::loadCurrent();
$user  = CMediusers::get();

// Chapitres
$where_chapitre          = array();
$where_chapitre["actif"] = " = '1'";

$chapitre          = new CAnesthPeropChapitre();
$chapitre->libelle = CAppUI::tr("common-No chapter");

$chapters = array(CAppUI::tr("common-No chapter") => $chapitre);

$chapitre  = new CAnesthPeropChapitre();
$chapitres = $chapitre->loadGroupList($where_chapitre, "libelle ASC");

CStoredObject::massLoadBackRefs($chapitres, "anesth_perop_categories");

foreach ($chapitres as $_chapitre) {
  $categories = $_chapitre->loadRefsCategoriesGestes();

  $chapters[$_chapitre->_view] = $_chapitre;
}

// Categories
$where_categorie                = array();
$where_categorie["actif"]       = " = '1'";
$where_categorie["chapitre_id"] = " IS NULL";

$categorie          = new CAnesthPeropCategorie();
$categorie->libelle = CAppUI::tr("common-No category");

$geste_categories = array(CAppUI::tr("common-No category") => $categorie);

$categorie  = new CAnesthPeropCategorie();
$categories = $categorie->loadGroupList($where_categorie, "libelle ASC");

foreach ($categories as $_categorie) {
  $_categorie->loadRefsGestesPerop(array(), 1, $see_all_gestes);
  $geste_categories[$_categorie->_view] = $_categorie;
}

// Gestes
$where_geste                 = array();
$where_geste["actif"]        = " = '1'";
$where_geste["categorie_id"] = " IS NULL";

if ($see_all_gestes) {
  $users     = $user->loadUsers();
  $functions = $group->loadFunctions();

  $where_geste[] = "user_id " .CSQLDataSource::prepareIn(array_keys($users)). " OR function_id " .CSQLDataSource::prepareIn(array_keys($functions)). " OR group_id = '$group->_id'";
}
else {
  $function = $user->loadRefFunction();

  $where_geste[] = "user_id = '$user->_id' OR function_id = '$function->_id' OR group_id = '$group->_id'";
}

$geste  = new CGestePerop();
$gestes = $geste->loadList($where_geste, "libelle ASC");

foreach ($gestes as $_geste) {
  $_geste->loadRefPrecisions();
}

$operation = COperation::find($operation_id);

// Lock add new or edit event
$limit_date_min    = null;

if ($operation->entree_reveil && ($type == 'sspi')) {
  $limit_date_min = $operation->entree_reveil;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("datetime"          , $datetime);
$smarty->assign("operation_id"      , $operation_id);
$smarty->assign("chapters"          , $chapters);
$smarty->assign("categories"        , $geste_categories);
$smarty->assign("gestes"            , $gestes);
$smarty->assign("chapitre_selected" , true);
$smarty->assign("categorie_selected", true);
$smarty->assign("element_selected"  , "selected");
$smarty->assign("see_all_gestes"    , $see_all_gestes);
$smarty->assign("limit_date_min"    , $limit_date_min);
$smarty->assign("type"              , $type);
$smarty->display("inc_vw_menu_structure_gestes_perop");

