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
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Repas\CPlat;
use Ox\Mediboard\Repas\CRepas;
use Ox\Mediboard\Repas\CTypeRepas;

CCanDo::checkEdit();
$group = CGroups::loadCurrent();

$ds         = CSQLDataSource::get("std");
$service_id = CValue::getOrSession("service_id", null);
$type       = CValue::getOrSession("type", null);
$date       = CValue::getOrSession("date", CMbDT::date());

$listRepas          = new CRepas;
$where              = array();
$where["group_id"]  = $ds->prepare("= %", $group->_id);
$where["date"]      = $ds->prepare("= %", $date);
$where["typerepas"] = $ds->prepare("= %", $type);

$ljoin     = array("menu" => "repas.menu_id = menu.menu_id");
$listRepas = $listRepas->loadList($where, null, null, null, $ljoin);

$plats      = new CPlat;
$typesPlats = $plats->_specs["type"]->_list;

$listMenu         = array();
$listPlat         = array();
$listRemplacement = array();
foreach ($typesPlats as $typePlat) {
  $listPlat[$typePlat] = array();
}

foreach ($listRepas as $keyRepas => &$repas) {
  $repas->loadRefMenu();
  $menu =& $listMenu[$repas->menu_id];
  if (!isset($listMenu[$repas->menu_id])) {
    $menu["obj"]   =& $repas->_ref_menu;
    $menu["total"] = 1;
    foreach ($typesPlats as $typePlat) {
      $menu["detail"][$typePlat] = 0;
    }
  }
  else {
    $menu["total"]++;
  }

  foreach ($typesPlats as $typePlat) {
    $plat_id =& $repas->$typePlat;
    if ($plat_id) {
      $plat_remplacement =& $listRemplacement[$typePlat][$plat_id];
      if (isset($plat_remplacement)) {
        $plat_remplacement["nb"]++;
      }
      else {
        $plats2 = new CPlat;
        $plats2->load($plat_id);
        $plat_remplacement = array("obj" => $plats2, "nb" => 1);
      }
    }
    else {
      $menu["detail"][$typePlat]++;
    }
  }
}
// Liste des types de repas
$listTypeRepas = new CTypeRepas;
$order         = "debut, fin, nom";
$listTypeRepas = $listTypeRepas->loadList(null, $order);

// Liste des services
$services           = new CService();
$where              = array();
$where["group_id"]  = "= '$group->_id'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $services->loadListWithPerms(PERM_READ, $where, $order);
foreach ($services as &$service) {
  $service->validationRepas($date);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("listMenu", $listMenu);
$smarty->assign("listRemplacement", $listRemplacement);
$smarty->assign("date", $date);
$smarty->assign("plats", $plats);
$smarty->assign("listTypeRepas", $listTypeRepas);
$smarty->assign("type", $type);
$smarty->assign("services", $services);
$smarty->assign("service_id", $service_id);

$smarty->display("vw_quantite.tpl");