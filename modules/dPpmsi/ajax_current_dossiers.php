<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$date_min = CView::get("date_min", "date default|".CMbDT::date('-1 day'), true);
$date_max = CView::get("date_max", "date default|now", true);
$types    = CView::get("types", "str", true);
CView::checkin();

//Préparation
$types_where = false;
if ($types && !in_array("", $types)) {
  $types_where = CSQLDataSource::prepareIn($types);
}

// Selection des salles
$listSalles = new CSalle;
$listSalles = $listSalles->loadGroupList();

$totalOp = 0;
$counts = array (
  "sejours" => array (
    "total" => 0,
    "facturees" => 0,
  ),
  "operations" => array (
    "total" => 0,
    "facturees" => 0,
  ),
  "urgences" => array (
    "total" => 0,
    "facturees" => 0,
  ),

);

/**
 * Comptage des Interventions planifiées
 */
$plage = new CPlageOp;
$where = array();
$where["plagesop.date"]     = "BETWEEN '$date_min' AND '$date_max'";
$where["plagesop.salle_id"] = CSQLDataSource::prepareIn(array_keys($listSalles));

/** @var CPlageOp[] $plages */
$plages = $plage->loadList($where);

$where = array(
  "annulee" => "= '0'",
);
$ljoin = array();
if ($types_where) {
  $ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
  $where["sejour.type"] = $types_where;
}
/** @var COperation[] $operations */
$operations = CPlageOp::massLoadBackRefs($plages, "operations", null, $where, $ljoin);

foreach ($plages as $_plage) {
  $_plage->_ref_operations = $_plage->_back["operations"];
  foreach ($_plage->_ref_operations as $_operation) {
    $counts["operations"]["total"]++;
    if ($_operation->facture) {
      $counts["operations"]["facturees"]++;
    }
  }
  $totalOp += count($_plage->_ref_operations);
}

/**
 * Comptage des Interventions hors plages
 */
$operation = new COperation;
$where = array();
$ljoin = array();
$ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
$where["operations.date"]       = "BETWEEN '$date_min' AND '$date_max'";
$where["operations.plageop_id"] = "IS NULL";
$where["operations.annulee"]    = "= '0'";
$where["sejour.group_id"]       = "= '".CGroups::loadCurrent()->_id."'";
if ($types_where) {
  $where["sejour.type"]         = $types_where;
}


/** @var COperation[] $horsplages */
$horsplages = $operation->loadList($where, null, null, null, $ljoin);
$totalOp += count($horsplages);
foreach ($horsplages as $_operation) {
  $counts["urgences"]["total"]++;
  if ($_operation->facture) {
    $counts["urgences"]["facturees"]++;
  }
}

/**
 * Comptage des séjours
 */

$group = CGroups::loadCurrent();
$sejour = new CSejour;
$where = array();
$where["entree"]   = "< '$date_max'";
$where["sortie"]   = "> '$date_min'";
$where["group_id"] = "= '$group->_id'";
$where["annule"]   = "= '0'";
if ($types_where) {
  $where["type"]   = $types_where;
}
$order = array();
$order[] = "sortie";
$order[] = "entree";

/** @var CSejour[] $listSejours */
$count = $sejour->countList($where);
$listSejours = $sejour->loadList($where, $order);
foreach ($listSejours as $_sejour) {
  $counts["sejours"]["total"]++;
  if ($_sejour->facture) {
    $counts["sejours"]["facturees"]++;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("counts"   , $counts);
$smarty->assign("totalOp"  , $totalOp);
$smarty->display("current_dossiers/inc_current_dossiers");

