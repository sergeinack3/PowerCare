<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;

CCanDo::checkRead();
$geste_perop_id = CView::get("geste_perop_id", "ref class|CGestePerop");
$precision_ids  = CView::get("precision_ids", "str");
$clickable      = CView::get("clickable", "bool default|0");
CView::checkin();

$where          = array();
$where["actif"] = " = '1'";

if ($precision_ids && !$geste_perop_id) {
  $precision_ids = explode("|", $precision_ids);
  CMbArray::removeValue("", $precision_ids);

  $where["geste_perop_precision_id"] = CSQLDataSource::prepareIn($precision_ids);
}
else {
  $where["geste_perop_id"] = " = '$geste_perop_id'";
}

if (!$precision_ids && $geste_perop_id === 0 && $clickable) {
  $where["geste_perop_precision_id"] = "IS NULL";
}

$precision  = new CGestePeropPrecision();
$precisions = $precision->loadGroupList($where, "libelle ASC");

CStoredObject::massLoadFwdRef($precisions, "geste_perop_id");

foreach ($precisions as $_precision) {
  $_precision->loadRefGestePerop();
  $_precision->loadRefValeurs();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("precisions"    , $precisions);
$smarty->assign("geste_selected", true);
$smarty->display("inc_vw_menu_geste_precisions");
