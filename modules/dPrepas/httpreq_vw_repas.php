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
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Repas\CPlat;
use Ox\Mediboard\Repas\CTypeRepas;

CCanDo::checkRead();
$group = CGroups::loadCurrent();

$type       = CValue::getOrSession("type", null);
$service_id = CValue::getOrSession("service_id", null);
$date       = CValue::getOrSession("date", CMbDT::date());

$service = null;

// Liste des services
$services           = new CService();
$where              = array();
$where["group_id"]  = "= '$group->_id'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $services->loadListWithPerms(PERM_READ, $where, $order);

// Type de repas
$typeRepas = new CTypeRepas;
$typeRepas->load($type);
$typeRepas_id =& $typeRepas->_id;

if (!$service_id || !array_key_exists($service_id, $services)) {
  CValue::setSession("service_id", null);
  $service_id = null;
}
else {
  $service =& $services[$service_id] .
    $service->validationRepas($date);
  $service->loadRefsBack();
  foreach ($service->_ref_chambres as $chambre_id => &$chambre) {
    $chambre->loadRefsBack();
    foreach ($chambre->_ref_lits as $lit_id => &$lit) {
      $lit->loadAffectations($date);
      $lit->checkDispo($date);
      foreach ($lit->_ref_affectations as $affectation_id => &$affectation) {
        $affectation->loadRefSejour();
        $affectation->loadMenu($date, array($typeRepas_id => null));
        $sejour =& $affectation->_ref_sejour;

        $date_entree  = substr($affectation->entree, 0, 10);
        $date_sortie  = substr($affectation->sortie, 0, 10);
        $heure_entree = substr($affectation->entree, 11, 5);
        $heure_sortie = substr($affectation->sortie, 11, 5);

        if (!$sejour->sejour_id || $sejour->type == "ambu" ||
          ($date == $date_entree && $heure_entree > $typeRepas->fin) ||
          ($date == $date_sortie && $heure_sortie < $typeRepas->debut)) {
          unset($lit->_ref_affectations[$affectation_id]);
        }
        else {
          $repas =& $affectation->_list_repas[$date][$typeRepas_id];
          $repas->loadRefMenu();
          $repas->loadRemplacements();
        }
      }
      if (!count($lit->_ref_affectations)) {
        unset($chambre->_ref_lits[$lit_id]);
      }
    }
    if (!count($chambre->_ref_lits)) {
      unset($service->_ref_chambres[$chambre_id]);
    }
  }
}

$plat = new CPlat();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("type", $typeRepas_id);
$smarty->assign("date", $date);
$smarty->assign("service", $service);
$smarty->assign("service_id", $service_id);
$smarty->assign("plat", $plat);
if ($type) {
  $smarty->assign("validation", $service->_ref_validrepas[$date][$type]);
}

$smarty->display("inc_vw_repas.tpl");