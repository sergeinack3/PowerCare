<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CGestePerop;
use Ox\Mediboard\SalleOp\CGestePeropPrecision;
use Ox\Mediboard\SalleOp\CPrecisionValeur;
use Ox\Mediboard\SalleOp\CProtocoleGestePeropItem;

CCanDo::checkRead();
$geste_perop_precision_id      = CView::get("precision_id", "ref class|CGestePeropPrecision");
$geste_id                      = CView::get("geste_id", "ref class|CGestePerop");
$protocole_geste_perop_item_id = CView::get("protocole_geste_perop_item_id", "ref class|CProtocoleGestePeropItem");
$protocole_settings            = CView::get("protocole_settings", "bool default|0");
$checked_item                  = CView::get("checked_item", "bool default|1");
CView::checkin();

$where                             = [];
$where["actif"]                    = " = '1'";
$where["geste_perop_precision_id"] = " = '$geste_perop_precision_id'";

$valeur  = new CPrecisionValeur();
$valeurs = $valeur->loadGroupList($where, "valeur ASC");

$geste     = CGestePerop::findOrNew($geste_id);
$precision = CGestePeropPrecision::findOrNew($geste_perop_precision_id);

$protocole_item = CProtocoleGestePeropItem::findOrNew($protocole_geste_perop_item_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("valeurs", $valeurs);
$smarty->assign("geste", $geste);
$smarty->assign("precision", $precision);
$smarty->assign("protocole_item", $protocole_item);
$smarty->assign("protocole_settings", $protocole_settings);
$smarty->assign("checked_item", $checked_item);
$smarty->display("inc_vw_select_geste_valeurs");
