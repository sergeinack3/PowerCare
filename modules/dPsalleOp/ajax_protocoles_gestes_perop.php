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
use Ox\Mediboard\SalleOp\CProtocoleGestePerop;

CCanDo::checkEdit();
$group_id    = CView::get("group_id", "ref class|CGroups");
$user_id     = CView::get("user_id", "ref class|CMediusers");
$function_id = CView::get("function_id", "ref class|CFunctions");
$page        = CView::get("page", "num default|0");
$keywords    = CView::get("keywords", "str", true);
CView::checkin();

$group = CGroups::loadCurrent();

$where = array();
$step = 30;

$protocole_geste_perop  = new CProtocoleGestePerop();
$order = "libelle ASC";
$limit = "$page, $step";

if ($keywords) {
  $where[] = "libelle LIKE '%$keywords%' OR description LIKE '%$keywords%'";
}
if ($user_id) {
  $where["user_id"] = "= '$user_id'";
}
if ($function_id) {
  $where["function_id"] = "='$function_id'";
}

if (!$user_id && !$function_id) {
  $functions = $group->loadFunctions();
  $user      = new CMediusers();
  $users     = $user->loadUsers();

  $where[] = "user_id " . CSQLDataSource::prepareIn(array_keys($users)) .
    " OR function_id " . CSQLDataSource::prepareIn(array_keys($functions)) .
    " OR group_id = '$group->_id'";
}

$protocoles_geste_perop = $protocole_geste_perop->loadList($where, $order, $limit);
$total                  = $protocole_geste_perop->countList($where);

CStoredObject::massLoadFwdRef($protocoles_geste_perop, "group_id");
CStoredObject::massLoadFwdRef($protocoles_geste_perop, "function_id");
CStoredObject::massLoadFwdRef($protocoles_geste_perop, "user_id");

foreach ($protocoles_geste_perop as $_protocole) {
  $_protocole->loadRefGroup();
  $_protocole->loadRefFunction();
  $_protocole->loadRefUser()->loadRefFunction();
  $_protocole->_count_items = count($_protocole->loadRefsProtocoleGestePeropItems());
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page"                  , $page);
$smarty->assign("total"                 , $total);
$smarty->assign("step"                  , $step);
$smarty->assign("protocoles_geste_perop", $protocoles_geste_perop);
$smarty->display("inc_protocoles_gestes_perop");
