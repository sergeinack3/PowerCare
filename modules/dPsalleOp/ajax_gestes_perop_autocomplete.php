<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkRead();
$keywords    = CView::get('geste_perop_id_view', 'str');
$object_guid = CView::get('object_guid', 'str');
CView::checkin();
CView::enableSlave();

$gestes = array();

if ($object_guid) {
  $object = CMbObject::loadFromGuid($object_guid);

  if ($object instanceof CAnesthPeropCategorie) {
    $gestes = $object->loadRefsGestesPerop();
  }
  elseif ($object instanceof CProtocoleGestePerop) {
    $gestes = $object->loadRefsGestePerop();
  }
}

$group     = CGroups::loadCurrent();
$functions = $group->loadFunctions();
$user      = new CMediusers();
$users     = $user->loadUsers();

$where = array();
if ($keywords) {
  $where["libelle"] = " LIKE '%$keywords%'";
}

if ($gestes) {
  $where[] = "geste_perop_id " . CSQLDataSource::prepareNotIn(array_keys($gestes));
}

$where[] = "group_id = '$group->_id' OR function_id " . CSQLDataSource::prepareIn(array_keys($functions)) . " 
            OR user_id " . CSQLDataSource::prepareIn(array_keys($users));

$where['actif'] = " = '1'";

$order        = "libelle ASC";
$geste_perop  = new CGestePerop();
$gestes_perop = $geste_perop->loadList($where, $order);

$smarty = new CSmartyDP();
$smarty->assign("matches", $gestes_perop);
$smarty->display("CGestesPerop_autocomplete");
