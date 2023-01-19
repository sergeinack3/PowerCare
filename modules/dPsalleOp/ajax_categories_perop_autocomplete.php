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
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;

CCanDo::checkRead();
$keywords    = CView::get('categorie_perop_id_view', 'str');
$object_guid = CView::get('object_guid', 'str');
CView::checkin();
CView::enableSlave();

$categories = array();
$where      = array();

if ($object_guid) {
  $object     = CMbObject::loadFromGuid($object_guid);
  $categories = $object->loadRefsCategoriesGestes();
}

if ($keywords) {
  $where["libelle"] = " LIKE '%$keywords%'";
}

if ($categories) {
  $where[] = "anesth_perop_categorie_id " . CSQLDataSource::prepareNotIn(array_keys($categories));
}

$where["actif"] = " = '1'";

$order            = "libelle ASC";
$categorie_perop  = new CAnesthPeropCategorie();
$categories_perop = $categorie_perop->loadGroupList($where, $order);

$smarty = new CSmartyDP();
$smarty->assign("matches", $categories_perop);
$smarty->display("CAnesthPeropCategorie_autocomplete");
