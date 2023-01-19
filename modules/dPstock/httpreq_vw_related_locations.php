<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CProductStockLocation;

CCanDo::checkRead();

$owner_guid          = CView::get("owner_guid", "str");
$owner_classes       = CView::get("owner_classes", "str");
$exclude_location_id = CView::get("exclude_location_id", "ref class|CProductStockLocation");

CView::checkin();

$parts = explode("-", $owner_guid);
$parts_classes = explode("-", $owner_classes);

CMbArray::removeValue("", $parts_classes);

$where = array(
  "object_class" => count($parts_classes) ? CSQLDataSource::prepareIn($parts_classes) : (" = '{$parts[0]}'"),
  "actif"        => "= '1'"
);

if (isset($parts[1]) && $parts[1]) {
  $where["object_id"] = " = '{$parts[1]}'";
}

if ($exclude_location_id) {
  $where["stock_location_id"] = " != '$exclude_location_id'";
}

$location  = new CProductStockLocation();
$locations = $location->loadGroupList($where, "position, name");

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("locations", $locations);
$smarty->display("inc_autocomplete_related_locations");
