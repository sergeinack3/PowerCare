<?php

use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

function graphJoursPatients(
  $debut = null, $fin = null,
  $prat_id = 0, $service_id = 0,
  $type_adm = "", $discipline_id = 0,
  $septique = 0
) {
  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $group_id = CGroups::loadCurrent()->_id;

  $ds = CSQLDataSource::get("std");

  $ljoin_prat       = "";
  $where_prat       = "";
  $where_service    = "";
  $where_discipline = "";
  $where_type_adm   = "";
  $where_septique   = "";

  if ($prat_id) {
    $where_prat = "AND sejour.praticien_id = '$prat_id'";
  }

  if ($discipline_id) {
    $ljoin_prat       = "LEFT JOIN users_mediboard ON users_mediboard.user_id = sejour.praticien_id";
    $where_discipline = "AND users_mediboard.function_id = '$discipline_id'";
  }

  if ($service_id) {
    $where_service = "AND sejour.service_id = '$service_id'";
  }

  if ($type_adm) {
    if ($type_adm == 1) {
      $where_type_adm = "AND sejour.type IN ('comp', 'ambu')";
    }
    else {
      $where_type_adm = "AND sejour.type = '$type_adm'";
    }
  }

  if ($septique) {
    $where_septique = "AND sejour.septique = '1'";
  }

  $ticks       = array();
  $serie_total = array(
    "label"   => 'Total',
    "data"    => array(),
    "markers" => array('show' => true),
    "bars"    => array('show' => false)
  );

  $urgences_installed = CModule::getActive("dPurgences");

  $series = array();

  $serie = array(
    "data"  => array(),
    "label" => "Jours-patients"
  );

  for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 MONTH", $i)) {
    $ticks[] = array(count($ticks), CMbDT::transform("+0 DAY", $i, "%m/%Y"));

    $j = CMbDT::date("+1 MONTH", $i);

    $query = "SELECT
      SUM(DATEDIFF(
        IF (DATE(sejour.sortie) <= '$j', DATE(sejour.sortie), '$j'), 
        IF (DATE(sejour.entree) >  '$i', DATE(sejour.entree), '$i')
      )+1) AS nb_jours
    FROM sejour
    LEFT JOIN patients ON sejour.patient_id = patients.patient_id
    $ljoin_prat"
      . ($urgences_installed ? "LEFT JOIN rpu ON rpu.mutation_sejour_id = sejour.sejour_id" : "") .
      " WHERE sejour.group_id = '$group_id'
    AND sejour.entree <= '$j' 
    AND sejour.sortie > '$i'
    AND sejour.annule = '0'
    AND sejour.type != 'exte'
    $where_prat
    $where_discipline
    $where_service
    $where_type_adm
    $where_septique"
      . ($urgences_installed ? "AND rpu.rpu_id IS NULL" : "");

    $serie["data"][] = array(count($ticks) - 1, $ds->loadResult($query));
  }

  $series[] = $serie;

  $options = array(
    'title'       => "Evolution du nombre de jours-patients",
    'xaxis'       => array('labelsAngle' => 45, 'ticks' => $ticks),
    'yaxis'       => array('min' => 0, 'autoscaleMargin' => 1),
    'bars'        => array('show' => true, 'stacked' => true, 'barWidth' => 0.8),
    'HtmlText'    => false,
    'legend'      => array('show' => true, 'position' => 'nw'),
    'grid'        => array('verticalLines' => false),
    'spreadsheet' => array(
      'show'             => true,
      'csvFileSeparator' => ';',
      'decimalSeparator' => ',',
      'tabGraphLabel'    => 'Graphique',
      'tabDataLabel'     => 'Données',
      'toolbarDownload'  => 'Fichier CSV',
      'toolbarSelectAll' => 'Sélectionner tout le tableau'
    )
  );

  return array('series' => $series, 'options' => $options);
}

function detailJoursPatient(
  $debut, $fin,
  $prat_id = 0, $service_id = 0,
  $type_adm = "", $discipline_id = 0,
  $septique = 0) {

  $group_id = CGroups::loadCurrent()->_id;

  $ds = CSQLDataSource::get("std");

  $urgences_installed = CModule::getActive("dPurgences");

  $ljoin_prat       = "";
  $where_prat       = "";
  $where_service    = "";
  $where_discipline = "";
  $where_type_adm   = "";
  $where_septique   = "";

  if ($prat_id) {
    $where_prat = "AND sejour.praticien_id = '$prat_id'";
  }

  if ($discipline_id) {
    $ljoin_prat       = "LEFT JOIN users_mediboard ON users_mediboard.user_id = sejour.praticien_id";
    $where_discipline = "AND users_mediboard.function_id = '$discipline_id'";
  }

  if ($service_id) {
    $where_service = "AND sejour.service_id = '$service_id'";
  }

  if ($type_adm) {
    if ($type_adm == 1) {
      $where_type_adm = "AND sejour.type IN ('comp', 'ambu')";
    }
    else {
      $where_type_adm = "AND sejour.type = '$type_adm'";
    }
  }

  if ($septique) {
    $where_septique = "AND sejour.septique = '1'";
  }

  $query = "SELECT 
    patients.nom, patients.prenom, 
    sejour.entree, sejour.sortie, 
    IF (DATE(sejour.entree) >  '$debut', DATE(sejour.entree), '$debut') AS entree_bornee,
    IF (DATE(sejour.sortie) <= '$fin', DATE(sejour.sortie), '$fin') AS sortie_bornee,
    DATEDIFF(
      IF (DATE(sejour.sortie) <= '$fin', DATE(sejour.sortie), '$fin'), 
      IF (DATE(sejour.entree) >  '$debut', DATE(sejour.entree), '$debut')
    )+1 AS nb_jours
  FROM sejour
  LEFT JOIN patients ON sejour.patient_id = patients.patient_id
  $ljoin_prat"
    . ($urgences_installed ? "LEFT JOIN rpu ON rpu.mutation_sejour_id = sejour.sejour_id" : "") .
    " WHERE sejour.group_id = '$group_id'
  AND sejour.entree <= '$fin' 
  AND sejour.sortie > '$debut'
  AND sejour.annule = '0'
  AND sejour.type != 'exte'
  $where_prat
  $where_discipline
  $where_service
  $where_type_adm
  $where_septique"
    . ($urgences_installed ? "AND rpu.rpu_id IS NULL" : "") .
    " GROUP BY patients.patient_id
   ORDER BY patients.nom, patients.prenom";

  return $ds->loadList($query);
}
