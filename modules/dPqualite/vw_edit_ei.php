<?php
/**
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Qualite\CEiCategorie;
use Ox\Mediboard\Qualite\CEiItem;

CCanDo::checkAdmin();

$ei_categorie_id = CValue::getOrSession("ei_categorie_id", 0);
$ei_item_id      = CValue::getOrSession("ei_item_id", 0);
$vue_item        = CValue::getOrSession("vue_item", 0);


// Catégorie demandée
$categorie = new CEiCategorie();
if (!$categorie->load($ei_categorie_id)) {
  // Cette catégorie n'est pas valide
  $ei_categorie_id = null;
  CValue::setSession("ei_categorie_id");
  $categorie = new CEiCategorie();
}
else {
  $categorie->loadRefsBack();
}

// Item demandé
$item = new CEiItem;
if (!$item->load($ei_item_id)) {
  // Cet item n'est pas valide
  $ei_item_id = null;
  CValue::setSession("ei_item_id");
  $item = new CEiItem();
}
else {
  $item->loadRefsFwd();
}

// Liste des Catégories
$listCategories = $categorie->loadList(null, "nom");

// Liste des Items
$where = null;
if ($vue_item) {
  $where = "ei_categorie_id = '$vue_item'";
}
/** @var CEiItem[] $listItems */
$listItems = $item->loadList($where, "ei_categorie_id, nom");
foreach ($listItems as $_item) {
  $_item->loadRefsFwd();
}

$smarty = new CSmartyDP();

$smarty->assign("categorie", $categorie);
$smarty->assign("item", $item);
$smarty->assign("listCategories", $listCategories);
$smarty->assign("listItems", $listItems);
$smarty->assign("vue_item", $vue_item);

$smarty->display("vw_edit_ei.tpl"); 
