<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$service_id    = CValue::get("service_id");
$type_data     = CValue::get("type_data");
$date_min      = CValue::get("_date_min");
$date_max      = CValue::get("_date_max");
$prat_id       = CValue::get("prat_id");
$discipline_id = CValue::get("discipline_id");
$type          = CValue::get("type");
$septique      = CValue::get("septique");

CView::enforceSlave();

if ($date_min > $date_max) {
  list($date_min, $date_max) = array($date_max, $date_min);
}

$ds = CSQLDataSource::get("std");

$group_id = CGroups::loadCurrent()->_id;

$results = array();

$entree = "entree_prevue";
$sortie = "sortie_prevue";

if ($type_data == "reelle") {
  $entree = "entree_reelle";
  $sortie = "sortie_reelle";
}

$common_query = "SELECT count(*) as total, service.nom as service
  FROM sejour ";

$common_left_join = " LEFT JOIN affectation ON affectation.sejour_id = sejour.sejour_id
  LEFT JOIN service ON service.service_id = affectation.service_id";

$common_where = " WHERE sejour.$entree BETWEEN '$date_min' AND '$date_max'
  AND sejour.$entree IS NOT NULL
  AND sejour.$sortie IS NOT NULL
  AND affectation.affectation_id IS NOT NULL
  AND sejour.group_id = '$group_id' ";

if ($service_id) {
  $common_where .= " AND affectation.service_id = '$service_id' ";
}

if ($prat_id) {
  $common_where .= " AND sejour.praticien_id = '$prat_id' ";
}

if ($discipline_id) {
  $common_left_join .= " LEFT JOIN users_mediboard ON users_mediboard.user_id = sejour.praticien_id ";
  $common_where     .= " AND users_mediboard.discipline_id = '$discipline_id' ";
}

if ($septique) {
  $common_where .= " AND sejour.septique = '$septique' ";
}

if ($type) {
  if ($type == 1) {
    $common_where .= " AND sejour.type IN('comp', 'ambu') ";
  }
  else {
    $common_where .= " AND sejour.type = '$type' ";
  }
}

$common_group_by = "GROUP BY affectation.service_id";

// Nombre de patients avec entrée réelle dans la période demandée
$query = $common_query . $common_left_join . $common_where;

$query .= $common_group_by;

$result = $ds->loadList($query);

foreach ($result as $_result) {
  @$results[$_result["service"]]["patients"] = $_result["total"];
}

// Nombre de patients dont la sortie a été faite avant minuit le jour de leur entrée
$query = "SELECT count(*) as total, service.nom as service
  FROM sejour
  LEFT JOIN affectation ON affectation.sejour_id = sejour.sejour_id
  LEFT JOIN service ON service.service_id = affectation.service_id
  WHERE ";

$query = $common_query . $common_left_join . $common_where;
$query .= " AND DATE_FORMAT(sejour.$entree, '%Y-%m-%d') = DATE_FORMAT(sejour.$sortie, '%Y-%m-%d')";
$query .= $common_group_by;

$result = $ds->loadList($query);

foreach ($result as $_result) {
  @$results[$_result["service"]]["ambu"] = $_result["total"];
}


// Nombre de patients restant après minuit le jour de leur entrée
$query = $common_query . $common_left_join . $common_where;
$query .= " AND DATE_FORMAT(sejour.$entree, '%Y-%m-%d') < DATE_FORMAT(sejour.$sortie, '%Y-%m-%d')";
$query .= $common_group_by;

$result = $ds->loadList($query);

foreach ($result as $_result) {
  @$results[$_result["service"]]["hospi"] = $_result["total"];
}

ksort($results);


$smarty = new CSmartyDP();

$smarty->assign("results", $results);

$smarty->display("inc_patient_by_type_by_service.tpl");