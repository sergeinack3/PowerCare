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
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;

CCanDo::checkEdit();
$geste_perop_id = CView::get("geste_perop_id", "ref class|CGestePerop");
CView::checkin();

$geste_perop = new CGestePerop();
$geste_perop->load($geste_perop_id);

$where_precision                   = array();
$where_precision["geste_perop_id"] = "= '$geste_perop->_id'";

$precision   = new CGestePeropPrecision();
$precisions = $precision->loadGroupList($where_precision, "libelle ASC");

CStoredObject::massLoadFwdRef($precisions, "group_id");
CStoredObject::massLoadFwdRef($precisions, "geste_perop_id");
CStoredObject::massLoadBackRefs($precisions, "precision_valeurs");

foreach ($precisions as $_precision) {
  $_precision->loadRefGroup();
  $_precision->loadRefGestePerop();
  $_precision->loadRefValeurs();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("precisions", $precisions);
$smarty->assign("geste_perop", $geste_perop);
$smarty->display("inc_vw_list_precisions");
