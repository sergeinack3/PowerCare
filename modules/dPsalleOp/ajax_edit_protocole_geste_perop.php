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
use Ox\Mediboard\SalleOp\CAnesthPeropCategorie;
use Ox\Mediboard\SalleOp\CProtocoleGestePerop;
use Ox\Mediboard\SalleOp\CProtocoleGestePeropItem;

CCanDo::checkEdit();
$protocole_geste_perop_id = CView::get("protocole_geste_perop_id", "ref class|CProtocoleGestePerop");
CView::checkin();

$protocole_geste_perop = new CProtocoleGestePerop();
$protocole_geste_perop->load($protocole_geste_perop_id);

$protocole_geste_perop->loadRefGroup();
$protocole_geste_perop->loadRefFunction();
$protocole_geste_perop->loadRefUser();
$protocole_geste_perop->loadRefsNotes();
$protocole_items = $protocole_geste_perop->loadRefsProtocoleGestePeropItems();

$total = $protocole_geste_perop->loadRefProtocoleGestePeropItemCategories();

foreach ($protocole_items as $_item) {
    $context = $_item->loadRefContext();
}

$evenement_category   = new CAnesthPeropCategorie();
$evenement_categories = $evenement_category->loadGroupList(["actif" => "= '1'"], "libelle");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("protocole_geste_perop", $protocole_geste_perop);
$smarty->assign("protocole_geste_perop_item", new CProtocoleGestePeropItem());
$smarty->assign("evenement_categories", $evenement_categories);
$smarty->assign("total", $total);
$smarty->display("inc_edit_protocole_geste_perop");
