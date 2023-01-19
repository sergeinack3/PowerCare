<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$filter = new CSejour();

$filter->_date_min_stat = CValue::get("_date_min_stat", CMbDT::date("-1 YEAR"));
$rectif                 = CMbDT::transform("+0 DAY", $filter->_date_min_stat, "%d") - 1;
$filter->_date_min_stat = CMbDT::date("-$rectif DAYS", $filter->_date_min_stat);

$filter->_date_max_stat = CValue::get("_date_max_stat", CMbDT::date());
$rectif                 = CMbDT::transform("+0 DAY", $filter->_date_max_stat, "%d") - 1;
$filter->_date_max_stat = CMbDT::date("-$rectif DAYS", $filter->_date_max_stat);
$filter->_date_max_stat = CMbDT::date("+ 1 MONTH", $filter->_date_max_stat);
$filter->_date_max_stat = CMbDT::date("-1 DAY", $filter->_date_max_stat);

$filter->_service     = CValue::get("service_id", 0);
$filter->type         = CValue::get("type", 1);
$filter->praticien_id = CValue::get("prat_id", 0);
$filter->_specialite  = CValue::get("discipline_id", 0);
$filter->septique     = CValue::get("septique", 0);

$type_data = CValue::get("type_data", "prevue");

CView::enforceSlave();

// Qualité de l'information
$qualite = array();

// Liste des séjours totaux
$query = "SELECT COUNT(DISTINCT sejour.sejour_id) AS total, 1 as group_field
      FROM sejour
      LEFT JOIN users_mediboard ON sejour.praticien_id = users_mediboard.user_id
      LEFT JOIN affectation ON sejour.sejour_id = affectation.sejour_id
      LEFT JOIN service ON affectation.service_id = service.service_id
      WHERE
        sejour.entree_prevue BETWEEN '$filter->_date_min_stat 00:00:00' AND '$filter->_date_max_stat 23:59:59' AND
        sejour.group_id = '" . CGroups::loadCurrent()->_id . "' AND
        sejour.annule = '0'";
if ($filter->_service) {
  $query .= "\nAND service.service_id = '$filter->_service'";
}
if ($filter->praticien_id) {
  $query .= "\nAND sejour.praticien_id = '$filter->praticien_id'";
}
if ($filter->_specialite) {
  $query .= "\nAND users_mediboard.discipline_id = '$filter->_specialite'";
}
if ($filter->septique) {
  $query .= "\nAND sejour.septique = '$filter->septique'";
}
if ($filter->type) {
  if ($filter->type == 1) {
    $query .= "\nAND (sejour.type = 'comp' OR sejour.type = 'ambu')";
  }
  else {
    $query .= "\nAND sejour.type = '$filter->type'";
  }
}
$query  .= "\nGROUP BY group_field";
$sejour = new CSejour();
$result = $sejour->_spec->ds->loadlist($query);

$qualite["total"] = 0;
if (count($result)) {
  $qualite["total"] = $result[0]["total"];
}

// 1. Patients placés
$query = "SELECT COUNT(sejour.sejour_id) AS total, 1 as group_field
      FROM sejour
      LEFT JOIN users_mediboard ON sejour.praticien_id = users_mediboard.user_id
      LEFT JOIN affectation ON sejour.sejour_id = affectation.sejour_id
      LEFT JOIN service ON affectation.service_id = service.service_id
      WHERE
        sejour.entree_prevue BETWEEN '$filter->_date_min_stat 00:00:00' AND '$filter->_date_max_stat 23:59:59' AND
        sejour.group_id = '" . CGroups::loadCurrent()->_id . "' AND
        sejour.annule = '0' AND
        affectation.affectation_id IS NOT NULL";
if ($filter->_service) {
  $query .= "\nAND service.service_id = '$filter->_service'";
}
if ($filter->praticien_id) {
  $query .= "\nAND sejour.praticien_id = '$filter->praticien_id'";
}
if ($filter->_specialite) {
  $query .= "\nAND users_mediboard.discipline_id = '$filter->_specialite'";
}
if ($filter->septique) {
  $query .= "\nAND sejour.septique = '$filter->septique'";
}
if ($filter->type) {
  if ($filter->type == 1) {
    $query .= "\nAND (sejour.type = 'comp' OR sejour.type = 'ambu')";
  }
  else {
    $query .= "\nAND sejour.type = '$filter->type'";
  }
}
$query  .= "\nGROUP BY group_field";
$sejour = new CSejour;
$result = $sejour->_spec->ds->loadlist($query);

$qualite["places"]["total"] = 0;
$qualite["places"]["pct"]   = 0;

if (count($result)) {
  $qualite["places"]["total"] = $result[0]["total"];
  $qualite["places"]["pct"]   = $result[0]["total"] / $qualite["total"] * 100;
}

// 2. Séjours sans entrées ou sorties réelles
$query = "SELECT COUNT(DISTINCT sejour.sejour_id) AS total, 1 as group_field
      FROM sejour
      LEFT JOIN users_mediboard ON sejour.praticien_id = users_mediboard.user_id
      LEFT JOIN affectation ON sejour.sejour_id = affectation.sejour_id
      LEFT JOIN service ON affectation.service_id = service.service_id
      WHERE
        sejour.entree_prevue BETWEEN '$filter->_date_min_stat 00:00:00' AND '$filter->_date_max_stat 23:59:59' AND
        sejour.group_id = '" . CGroups::loadCurrent()->_id . "' AND
        sejour.annule = '0' AND
        sejour.entree_reelle IS NOT NULL AND
        sejour.sortie_reelle IS NOT NULL";
if ($filter->_service) {
  $query .= "\nAND service.service_id = '$filter->_service'";
}
if ($filter->praticien_id) {
  $query .= "\nAND sejour.praticien_id = '$filter->praticien_id'";
}
if ($filter->_specialite) {
  $query .= "\nAND users_mediboard.discipline_id = '$filter->_specialite'";
}
if ($filter->septique) {
  $query .= "\nAND sejour.septique = '$filter->septique'";
}
if ($filter->type) {
  if ($filter->type == 1) {
    $query .= "\nAND (sejour.type = 'comp' OR sejour.type = 'ambu')";
  }
  else {
    $query .= "\nAND sejour.type = '$filter->type'";
  }
}
$query  .= "\nGROUP BY group_field";
$sejour = new CSejour;
$result = $sejour->_spec->ds->loadlist($query);

$qualite["reels"]["total"] = 0;
$qualite["reels"]["pct"]   = 0;

if (count($result)) {
  $qualite["reels"]["total"] = $result[0]["total"];
  $qualite["reels"]["pct"]   = $result[0]["total"] / $qualite["total"] * 100;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("qualite", $qualite);

$smarty->display("vw_hospi_qualite_donnees.tpl");