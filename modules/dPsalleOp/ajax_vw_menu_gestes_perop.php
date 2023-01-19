<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkRead();
$categorie_id   = CView::get("categorie_id", "ref class|CAnesthPeropCategorie");
$geste_ids      = CView::get("geste_ids", "str");
$clickable      = CView::get("clickable", "bool default|0");
$see_all_gestes = CView::get("see_all_gestes", "bool default|0", true);
CView::checkin();

$group = CGroups::loadCurrent();
$user  = CMediusers::get();

$where          = array();
$where["actif"] = " = '1'";

if ($geste_ids && !$categorie_id) {
  $geste_ids = explode("|", $geste_ids);
  CMbArray::removeValue("", $geste_ids);

  $where["geste_perop_id"] = CSQLDataSource::prepareIn($geste_ids);
}
else {
  $where["categorie_id"] = $categorie_id ? " = '$categorie_id'" : " IS NULL";
}

if ($see_all_gestes) {
  $users     = $user->loadUsers();
  $functions = $group->loadFunctions();

  $where[] = "user_id " .CSQLDataSource::prepareIn(array_keys($users)). " OR function_id " .CSQLDataSource::prepareIn(array_keys($functions)). " OR group_id = '$group->_id'";
}
else {
  $function = $user->loadRefFunction();

  $where[] = "user_id = '$user->_id' OR function_id = '$function->_id' OR group_id = '$group->_id'";
}

if ($categorie_id === 0 && !$geste_ids && $clickable) {
  $where["geste_perop_id"] = "IS NULL";
}

$geste  = new CGestePerop();
$gestes = $geste->loadList($where, "libelle ASC");

foreach ($gestes as $_geste) {
  $_geste->loadRefPrecisions();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("gestes"            , $gestes);
$smarty->assign("categorie_selected", true);
$smarty->assign("see_all_gestes"    , $see_all_gestes);
$smarty->display("inc_vw_menu_gestes_perop");
