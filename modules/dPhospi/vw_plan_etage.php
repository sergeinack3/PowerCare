<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();
// Récupération des paramètres
$services_id = CView::get("services_id", "str", true);
$refresh     = CView::get("refresh", "bool default|0");
CView::checkin();

$service_selected = explode(",", $services_id);
CMbArray::removeValue("", $service_selected);

//Chargement de tous les services
$service            = new CService();
$where              = array();
$where["group_id"]  = "= '" . CGroups::loadCurrent()->_id . "'";
$where["cancelled"] = "= '0'";
$services           = $service->loadGroupList($where, "nom ASC", null, "service_id");

$chambre                     = new CChambre();
$ljoin                       = array();
$ljoin["service"]            = "service.service_id = chambre.service_id";
$where                       = array();
$where["chambre.service_id"] = CSQLDataSource::prepareIn($service_selected);
$chambres_service            = $chambre->loadGroupList($where, "service.nom", null, "chambre.chambre_id", $ljoin);

$ljoin                       = array();
$ljoin["service"]            = "service.service_id = chambre.service_id";
$ljoin["emplacement"]        = "emplacement.chambre_id = chambre.chambre_id";
$where                       = array();
$where["emplacement.plan_x"] = "IS NOT NULL";
$where["emplacement.plan_y"] = "IS NOT NULL";
$where["service.service_id"] = CSQLDataSource::prepareIn($service_selected);
$where["service.group_id"]   = "= '" . CGroups::loadCurrent()->_id . "'";
$chambre_places              = $chambre->loadGroupList($where, null, null, "chambre_id", $ljoin);

$chambres_non_placees = $chambres_service;
if (count($chambre_places)) {
  $ljoin            = array();
  $ljoin["service"] = "service.service_id = chambre.service_id";

  $where                       = array();
  $where["chambre.annule"]     = " = '0'";
  $where["chambre.service_id"] = CSQLDataSource::prepareIn($service_selected);
  $where["chambre.chambre_id"] = CSQLDataSource::prepareNotIn(array_keys($chambre_places));
  $chambres_non_placees        = $chambre->loadGroupList($where, "service.nom", null, "chambre_id", $ljoin);
}

$chambres_np = array();
foreach ($chambres_non_placees as $ch) {
  /* @var CChambre $ch */
  $ch->loadRefService();
  $ch->loadRefEmplacement();
  $chambres_np[$ch->service_id][] = $ch;
}

$warning          = false;
$conf_nb_colonnes = CAppUI::gconf("dPhospi vue_topologique nb_colonnes_vue_topologique");
$grille           = array_fill(0, $conf_nb_colonnes, array_fill(0, $conf_nb_colonnes, array()));
if (count($service_selected)) {
  foreach ($chambre_places as $chambre) {
    /* @var CChambre $chambre */
    $chambre->loadRefService();
    $emplacement                                          = $chambre->loadRefEmplacement();
    $grille[$emplacement->plan_y][$emplacement->plan_x][] = $chambre;
    if (count($grille[$emplacement->plan_y][$emplacement->plan_x]) > 1) {
      $warning = true;
    }
    if ($emplacement->hauteur - 1) {
      for ($a = 0; $a <= $emplacement->hauteur - 1; $a++) {
        if ($emplacement->largeur - 1) {
          for ($b = 0; $b <= $emplacement->largeur - 1; $b++) {
            if ($b != 0) {
              unset($grille[$emplacement->plan_y + $a][$emplacement->plan_x + $b]);
            }
            elseif ($a != 0) {
              unset($grille[$emplacement->plan_y + $a][$emplacement->plan_x + $b]);
            }
          }
        }
        elseif ($a < $emplacement->hauteur - 1) {
          $c = $a + 1;
          unset($grille[$emplacement->plan_y + $c][$emplacement->plan_x]);
        }
      }
    }
    elseif ($emplacement->largeur - 1) {
      for ($b = 1; $b <= $emplacement->largeur - 1; $b++) {
        unset($grille[$emplacement->plan_y][$emplacement->plan_x + $b]);
      }
    }
  }
}

$service = null;
if (count($service_selected) == 1) {
  $service = $services[reset($service_selected)];
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("services", $services);
$smarty->assign("chambres_non_placees", $chambres_np);
$smarty->assign("service_selected", $service_selected);
$smarty->assign("grille", $grille);
$smarty->assign("service", $service);
$smarty->assign("warning", $warning);

if ($refresh) {
  $smarty->display("vw_plan_etage.tpl");
}
else {
  $smarty->display("vw_plan_etage.tpl");
}
