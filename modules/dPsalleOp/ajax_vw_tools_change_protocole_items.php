<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CProtocoleGestePeropItem;

CCanDo::checkEdit();
CView::checkin();

$group     = CGroups::loadCurrent();
$functions = $group->loadFunctions();
$user      = CMediusers::get();
$users     = $user->loadUsers();

$ljoin = array(
  "protocole_geste_perop"  => "protocole_geste_perop.protocole_geste_perop_id = protocole_geste_perop_item.protocole_geste_perop_id",
  "anesth_perop_categorie" => "protocole_geste_perop_item.object_id = anesth_perop_categorie.anesth_perop_categorie_id"
);

$where = array();
$where[] = "protocole_geste_perop.user_id " .CSQLDataSource::prepareIn(array_keys($users)). " OR protocole_geste_perop.function_id " .CSQLDataSource::prepareIn(array_keys($functions)). " OR protocole_geste_perop.group_id = '$group->_id'";
$where['protocole_geste_perop_item.object_class'] = "= 'CAnesthPeropCategorie'";

$protocole_item = new CProtocoleGestePeropItem();
$protocole_items = $protocole_item->loadList($where, null, null, null, $ljoin);

$counter = 0;

foreach ($protocole_items as $_item) {
  $category     = $_item->loadRefContext();
  $gestes_perop = $category->loadRefsGestesPerop();

  foreach ($gestes_perop as $_geste) {
    if ($_geste->_id) {
      $protocole_geste_perop_item                           = new CProtocoleGestePeropItem();
      $protocole_geste_perop_item->protocole_geste_perop_id = $_item->protocole_geste_perop_id;
      $protocole_geste_perop_item->object_class             = $_geste->_class;
      $protocole_geste_perop_item->object_id                = $_geste->_id;
      $protocole_geste_perop_item->rank                     = $_item->rank;
      $protocole_geste_perop_item->checked                  = $_item->checked;

      if ($msg = $protocole_geste_perop_item->store()) {
        return $msg;
      }
      $counter++;
    }
  }

  $_item->delete();
}

CAppUI::stepAjax(CAppUI::tr("CProtocoleGestePeropItem-msg-%s modified lines", $counter), UI_MSG_OK);
