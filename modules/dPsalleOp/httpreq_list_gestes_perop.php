<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkEdit();
$user_id       = CView::get("user_id", "ref class|CMediusers");
$function_id   = CView::get("function_id", "ref class|CFunctions");
$categorie_id  = CView::get("categorie_id", "ref class|CAnesthPeropCategorie");
$page          = CView::get("page", "num default|0");
$keywords      = CView::get("keywords", "str", true);
$current_group = CView::get("current_group", "bool default|0");
CView::checkin();

$group = CGroups::loadCurrent();

$where       = array();
$geste_perop = new CGestePerop();
$order       = "libelle ASC";
$limit       = "$page, 30";

if ($keywords) {
  $where[] = "libelle LIKE '%$keywords%' OR description LIKE '%$keywords%'";
}
if ($user_id) {
  $where["user_id"] = "= '$user_id'";
}
if ($function_id) {
  $where["function_id"] = "='$function_id'";
}
if ($categorie_id) {
  $where["categorie_id"] = "='$categorie_id'";
}
if ($current_group) {
  $where["group_id"] = "='$group->_id'";
}

if (!$user_id && !$function_id && !$current_group) {
  $functions = $group->loadFunctions();
  $user      = new CMediusers();
  $users     = $user->loadUsers();

  $where_custom = "user_id " . CSQLDataSource::prepareIn(array_keys($users)) . " OR group_id = '$group->_id'";

  if ($functions && count($functions)) {
    $where_custom .= " OR function_id " . CSQLDataSource::prepareIn(array_keys($functions));
  }

  $where[] = $where_custom;
}

$gestes_perop = $geste_perop->loadList($where, $order, $limit);
$nbResultat   = $geste_perop->countList($where);

CStoredObject::massLoadFwdRef($gestes_perop, "group_id");
CStoredObject::massLoadFwdRef($gestes_perop, "function_id");
CStoredObject::massLoadFwdRef($gestes_perop, "user_id");
CStoredObject::massLoadFwdRef($gestes_perop, "categorie_id");
CStoredObject::massLoadBackRefs($gestes_perop, "geste_perop_precisions");

foreach ($gestes_perop as $_geste) {
  $_geste->loadRefGroup();
  $_geste->loadRefFunction();
  $_geste->loadRefUser()->loadRefFunction();
  $_geste->loadRefCategory();
  $_geste->loadRefFile();
  $_geste->loadRefPrecisions();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("geste_perop" , $geste_perop);
$smarty->assign("gestes_perop", $gestes_perop);
$smarty->assign("nbResultat"  , $nbResultat);
$smarty->assign("page"        , $page);
$smarty->display("inc_list_gestes_perop");
