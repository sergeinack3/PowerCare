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
use Ox\Mediboard\SalleOp\CGestePeropPrecision;
use Ox\Mediboard\SalleOp\CPrecisionValeur;

CCanDo::checkEdit();
$precision_id = CView::get("precision_id", "ref class|CGestePeropPrecision");
CView::checkin();

$precision = new CGestePeropPrecision();
$precision->load($precision_id);

$where                             = array();
$where["geste_perop_precision_id"] = "= '$precision->_id'";

$precision_valeur  = new CPrecisionValeur();
$precision_valeurs = $precision_valeur->loadGroupList($where);

CStoredObject::massLoadFwdRef($precision_valeurs, "group_id");
CStoredObject::massLoadBackRefs($precision_valeurs, "anesth_perops");

foreach ($precision_valeurs as $_precision_valeur) {
  $_precision_valeur->loadRefGroup();
  $_precision_valeur->loadRefAnesthPerops();
}

CMbArray::naturalSort($precision_valeurs, array("valeur"));

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("precision_valeurs", $precision_valeurs);
$smarty->assign("precision"        , $precision);
$smarty->display("inc_vw_list_precision_valeurs");
