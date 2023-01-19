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
use Ox\Mediboard\SalleOp\CProtocoleGestePerop;

CCanDo::checkEdit();
$protocole_geste_perop_id = CView::get("protocole_geste_perop_id", "ref class|CProtocoleGestePerop");
CView::checkin();

$protocole_geste_perop = new CProtocoleGestePerop();
$protocole_geste_perop->load($protocole_geste_perop_id);

$protocole_items = $protocole_geste_perop->loadRefProtocoleGestePeropItemCategories();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("protocole_items"      , $protocole_items);
$smarty->assign("protocole_geste_perop", $protocole_geste_perop);
$smarty->display("inc_vw_protocole_geste_perop_items");
