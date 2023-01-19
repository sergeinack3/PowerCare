<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récuparation du graphique de répartition des patients en salle de reveil
 * par tranche horaire
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $bloc_id       Identifiant du bloc
 * @param int    $func_id       Identifiant de la fonction
 * @param int    $discipline_id Identifiant de la discipline
 * @param string $codeCCAM      Code CCAM
 *
 * @return array
 */
function graphPatParHeureReveil(
  $debut = null, $fin = null, $prat_id = 0, $bloc_id = 0,
  $func_id = 0, $discipline_id = null, $codeCCAM = ''
) {
  // This stats uses temporary table, impossible on slave
  // @todo Get rid of temporary table
  CView::disableSlave();

  $ds = CSQLDataSource::get("std");
  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $totalWorkDays = CMbDT::workDays($debut, $fin);

  $prat = new CMediusers;
  $prat->load($prat_id);

  $discipline = new CDiscipline;
  $discipline->load($discipline_id);

  $ticks = array();
  for ($i = 7; $i <= 21; $i++) {
    $ticks[] = array(count($ticks), CMbDT::transform("+0 DAY", "$i:00:00", "%Hh%M"));
  }

  $bloc  = new CBlocOperatoire();
  $blocs = $bloc->loadGroupList();
  if ($bloc_id) {
    $bloc->load($bloc_id);
  }

  $series = array();

  // Nombre de patients par heure
  foreach ($ticks as $i => $tick) {
    $hour  = str_replace('h', ':', $tick[1]) . ':00';
    $query = "DROP TEMPORARY TABLE IF EXISTS pat_par_heure";
    $ds->exec($query);
    $query = "CREATE TEMPORARY TABLE pat_par_heure
      SELECT COUNT(operations.operation_id) AS total_by_day,
             '" . $tick[1] . "' AS heure,
             operations.date AS date
      FROM operations
      INNER JOIN sallesbloc ON operations.salle_id = sallesbloc.salle_id
      LEFT JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
      WHERE sallesbloc.stats = '1'
      AND operations.date BETWEEN '$debut' AND '$fin'
      AND '$hour' BETWEEN TIME(operations.entree_reveil) AND TIME(operations.sortie_reveil_reel)
      AND operations.annulee = '0'";

    if ($prat_id) {
      $query .= "\nAND operations.chir_id = '$prat_id'";
    }
    if ($discipline_id) {
      $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
    }
    if ($codeCCAM) {
      $query .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
    }
    $query .= "\nAND sallesbloc.bloc_id " . CSQLDataSource::prepareIn(array_keys($blocs), $bloc_id);

    $query .= "\nGROUP BY operations.date";
    $ds->exec($query);

    $query  = "SELECT SUM(total_by_day) AS total, MAX(total_by_day) AS max,heure
                FROM pat_par_heure
                GROUP BY heure";
    $result = $ds->loadlist($query);
    if (count($result)) {
      $serie_moyenne["data"][] = array($i, $result[0]["total"] / $totalWorkDays);
      $serie_max["data"][]     = array($i, $result[0]["max"]);
    }
    else {
      $serie_moyenne["data"][] = array($i, 0);
      $serie_max["data"][]     = array($i, 0);
    }
  }

  // Nombre de patients non renseignés
  $query = "SELECT COUNT(operations.operation_id) AS total,
    'err' AS heure
    FROM operations
    INNER JOIN sallesbloc ON operations.salle_id = sallesbloc.salle_id
    LEFT JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
    WHERE sallesbloc.stats = '1'
    AND operations.date BETWEEN '$debut' AND '$fin'
    AND (operations.entree_reveil IS NULL OR operations.sortie_reveil_reel IS NULL)
    AND operations.annulee = '0'";

  if ($prat_id) {
    $query .= "\nAND operations.chir_id = '$prat_id'";
  }
  if ($discipline_id) {
    $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
  }
  if ($codeCCAM) {
    $query .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
  }

  if ($bloc_id) {
    $query .= "\nAND sallesbloc.bloc_id = '$bloc_id'";
  }

  $query  .= "\nGROUP BY heure";
  $result = $ds->loadlist($query);
  if (count($result)) {
    $serie_moyenne["data"][] = array(count($ticks), $result[0]["total"] / $totalWorkDays);
  }
  else {
    $serie_moyenne["data"][] = array(count($ticks), 0);
  }
  //$serie_max["data"][] = array(count($ticks), 0);
  $ticks[] = array(count($ticks), "Erreurs");

  $serie_moyenne["label"] = "moyenne";
  $serie_max["label"]     = "max";

  $series[] = $serie_moyenne;
  $series[] = $serie_max;

  // Set up the title for the graph
  $title    = "Patients moyens et max / heure du jour";
  $subtitle = "Moyenne sur tous les jours ouvrables";
  if ($prat_id) {
    $subtitle .= " - Dr $prat->_view";
  }
  if ($discipline_id) {
    $subtitle .= " - $discipline->_view";
  }
  if ($bloc_id) {
    $subtitle .= " - $bloc->_view";
  }
  if ($codeCCAM) {
    $subtitle .= " - CCAM : $codeCCAM";
  }

  $options = array(
    'title'       => $title,
    'subtitle'    => $subtitle,
    'xaxis'       => array('labelsAngle' => 45, 'ticks' => $ticks),
    'yaxis'       => array('autoscaleMargin' => 5, 'min' => 0),
    'lines'       => array('show' => true, 'filled' => true, 'fillColor' => '#999'),
    'markers'     => array('show' => true),
    'points'      => array('show' => true),
    'HtmlText'    => false,
    'legend'      => array('show' => true, 'position' => 'nw'),
    'mouse'       => array('track' => true, 'relative' => true, 'position' => 'ne'),
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
