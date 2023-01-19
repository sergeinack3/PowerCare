<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Repas\CMenu;
use Ox\Mediboard\Repas\CPlat;
use Ox\Mediboard\Repas\CTypeRepas;

CCanDo::checkEdit();
$ds    = CSQLDataSource::get("std");
$group = CGroups::loadCurrent();

$menu_id      = CValue::getOrSession("menu_id", null);
$plat_id      = CValue::getOrSession("plat_id", null);
$typerepas_id = CValue::getOrSession("typerepas_id", null);
$typeVue      = CValue::getOrSession("typeVue", 0);

// Liste des Type de Repas
$listTypeRepas = new CTypeRepas;
$where         = array("group_id" => $ds->prepare("= %", $group->_id));
$order         = "debut, fin, nom";
$listTypeRepas = $listTypeRepas->loadList($where, $order);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("typeVue", $typeVue);
$smarty->assign("listTypeRepas", $listTypeRepas);

if ($typeVue == 2) {
  // Chargement du type de repas demandé
  $typeRepas = new CTypeRepas;
  $typeRepas->load($typerepas_id);
  if ($typeRepas->group_id != $group->_id) {
    $typeRepas    = new CTypeRepas;
    $typerepas_id = null;
    CValue::setSession("typerepas_id", null);
  }

  $listHours = array();
  for ($i = 0; $i <= 23; $i++) {
    $key             = ($i <= 9) ? "0" . $i : $i;
    $listHours[$key] = $key;
  }

  $smarty->assign("listHours", $listHours);
  $smarty->assign("typeRepas", $typeRepas);
}
elseif ($typeVue == 1) {
  // Chargement du plat demandé
  $plat = new CPlat;
  $plat->load($plat_id);
  if ($plat->group_id != $group->_id) {
    $plat    = new CPlat;
    $plat_id = null;
    CValue::setSession("plat_id", null);
  }
  else {
    $plat->loadRefsFwd();
  }

  // Liste des plats
  $listPlats = new CPlat;
  $where     = array("group_id" => $ds->prepare("= %", $group->_id));
  $order     = "nom, type";
  $listPlats = $listPlats->loadList($where, $order);

  $smarty->assign("listPlats", $listPlats);
  $smarty->assign("plat", $plat);
}
else {

  // Chargement du menu demandé
  $menu = new CMenu;
  $menu->load($menu_id);
  if ($menu->group_id != $group->_id) {
    $menu    = new CMenu;
    $menu_id = null;
    CValue::setSession("menu_id", null);
  }

  // Liste des menus
  $listMenus = new CMenu;
  $where     = array("group_id" => $ds->prepare("= %", $group->_id));
  $order     = "nom";
  $listMenus = $listMenus->loadList($where, $order);

  foreach ($listMenus as $key => $value) {
    $listMenus[$key]->loadRefsFwd();
  }

  $listRepeat = range(1, 5);
  $typePlats  = new CPlat;

  $smarty->assign("typePlats", $typePlats);
  $smarty->assign("listRepeat", $listRepeat);
  $smarty->assign("date_debut", CMbDT::date());
  $smarty->assign("listTypeRepas", $listTypeRepas);
  $smarty->assign("listMenus", $listMenus);
  $smarty->assign("menu", $menu);
}

$smarty->display("vw_edit_menu.tpl");