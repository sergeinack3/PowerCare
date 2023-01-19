<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBesoinRessource;
use Ox\Mediboard\Bloc\CRessourceMaterielle;
use Ox\Mediboard\PlanningOp\COperation;

$type      = CValue::get("type");
$object_id = CValue::get("object_id");

$besoin = new CBesoinRessource();
$besoin->$type = $object_id;
/** @var CBesoinRessource[] $besoins */
$besoins = $besoin->loadMatchingList();

// Vert : tout va bien
$color = "";
$nb_besoins = count($besoins);
$nb_valides = 0;

if ($nb_besoins && $object_id) {
  $color = "0a0";
  /** @var COperation $operation */
  $operation = reset($besoins)->loadRefOperation();
  $operation->loadRefPlageOp();
  $deb_op = $operation->_datetime;
  $fin_op = CMbDT::addDateTime($operation->temp_operation, $deb_op);
  
  CMbObject::massLoadFwdRef($besoins, "type_ressource_id");
  
  foreach ($besoins as $_besoin) {
    $usage = $_besoin->loadRefUsage();
    
    $ressource = new CRessourceMaterielle();
    $ressource->type_ressource_id = $_besoin->type_ressource_id;
    if ($usage->_id) {
      $nb_valides++;
      $ressource = $usage->loadRefRessource();
    }
    $type_ressource = $_besoin->loadRefTypeRessource();
    $nb_ressources = $type_ressource->countBackRefs("ressources_materielles");
    
    // Check sur les indisponibilités
    $indispos = $ressource->loadRefsIndispos($deb_op, $fin_op);
    
    // Check sur les besoins
    $besoins = $ressource->loadRefsBesoins($deb_op, $fin_op);
    unset($besoins[$_besoin->_id]);
    
    // Check sur les usages
    $usages = $ressource->loadRefsUsages($deb_op, $fin_op);
    if ($usage->_id) {
      unset($usages[$usage->_id]);
    }
    
    if (count($indispos) + count($besoins) + count($usages) >= $nb_ressources) {
      $color = "a00";
      break;
    }
  }
}

CApp::json(
  array(
    "color" => $color,
    "count" => "$nb_valides/$nb_besoins",
  )
);
