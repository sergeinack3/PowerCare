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
use Ox\Mediboard\SalleOp\CPrecisionValeur;

CCanDo::checkRead();
$geste_perop_precision_id = CView::get("precision_id", "ref class|CGestePeropPrecision");
$clickable                = CView::get("clickable", "bool default|0");
CView::checkin();

$where                             = array();
$where["actif"]                    = " = '1'";
$where["geste_perop_precision_id"] = " = '$geste_perop_precision_id'";

if ($geste_perop_precision_id === 0 && $clickable) {
  $where["precision_valeur_id"] = "IS NULL";
}

$valeur  = new CPrecisionValeur();
$valeurs = $valeur->loadGroupList($where, "valeur ASC");

$precisions = CStoredObject::massLoadFwdRef($valeurs, "geste_perop_precision_id");
CStoredObject::massLoadFwdRef($precisions, "geste_perop_id");

foreach ($valeurs as $_valeur) {
  $precision = $_valeur->loadRefPrecision();
  $precision->loadRefGestePerop();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("valeurs"           , $valeurs);
$smarty->assign("precision_selected", true);
$smarty->display("inc_vw_menu_geste_precision_valeurs");
