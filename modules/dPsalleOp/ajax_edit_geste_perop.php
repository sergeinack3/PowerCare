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
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;

CCanDo::checkEdit();
$geste_perop_id = CView::get("geste_perop_id", "ref class|CGestePerop");
CView::checkin();

$geste_perop = new CGestePerop();
$geste_perop->load($geste_perop_id);

$geste_perop->loadRefGroup();
$geste_perop->loadRefFunction();
$geste_perop->loadRefUser();
$geste_perop->loadRefFile();

$evenement_category   = new CAnesthPeropCategorie();
$evenement_categories = $evenement_category->loadGroupList(array("actif" => " = '1'"), "libelle ASC");

$where_precision                   = array();
$where_precision["geste_perop_id"] = "= '$geste_perop_id'";

$precision  = new CGestePeropPrecision();
$precisions = $precision->loadGroupList($where_precision, "libelle ASC");

CStoredObject::massLoadBackRefs($precisions, "precision_valeurs");

foreach ($precisions as $_precision) {
  $_precision->loadRefValeurs();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("geste_perop"         , $geste_perop);
$smarty->assign("evenement_categories", $evenement_categories);
$smarty->assign("precisions"          , $precisions);
$smarty->display("inc_edit_geste_perop");
