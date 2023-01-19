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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;
use Ox\Mediboard\SalleOp\CPrecisionValeur;

CCanDo::checkEdit();
$precision_id   = CView::get("precision_id", "ref class|CGestePeropPrecision");
$geste_perop_id = CView::get("geste_perop_id", "ref class|CGestePerop");
CView::checkin();

$group = CGroups::loadCurrent();

$precision = new CGestePeropPrecision();
$precision->load($precision_id);
$valeurs = $precision->loadRefValeurs();
$precision->loadRefGestePerop();

// Select current group for a new object
if (!$precision->_id) {
  $precision->group_id = $group->_id;
  $precision->geste_perop_id = $geste_perop_id;
}

CStoredObject::massLoadBackRefs($valeurs, "anesth_perops");

foreach ($valeurs as $_valeur) {
  $_valeur->loadRefAnesthPerops();
}

CMbArray::naturalSort($valeurs, array("valeur"));

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("precision", $precision);
$smarty->assign("valeurs"  , $valeurs);
$smarty->display("inc_edit_precision");

