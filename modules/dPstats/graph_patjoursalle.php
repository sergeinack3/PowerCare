<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récupération du graphique du nombre de patient par jour et par salle
 * au bloc opératoire
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $salle_id      Identifiant de la salle
 * @param int    $bloc_id       Identifiant du bloc
 * @param int    $discipline_id Identifiant de la discipline
 * @param string $codeCCAM      Code CCAM
 * @param bool   $hors_plage    Prise en compte des hors plage
 *
 * @return array
 */
function graphPatJourSalle(
  $debut = null, $fin = null, $prat_id = 0, $salle_id = 0, $bloc_id = 0,
  $func_id = 0, $discipline_id = null, $codeCCAM = '', $hors_plage = true
) {
  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $prat = new CMediusers;
  $prat->load($prat_id);

  $bloc  = new CBlocOperatoire();
  $blocs = $bloc->loadGroupList();
  if ($bloc_id) {
    $bloc->load($bloc_id);
  }

  $salle = new CSalle;
  $salle->load($salle_id);

  $discipline = new CDiscipline;
  $discipline->load($discipline_id);

  $ticks = array();
  for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 MONTH", $i)) {
    $ticks[] = array(count($ticks), CMbDT::transform("+0 DAY", $i, "%m/%Y"));
  }

  // Gestion du hors plage
  $where_hors_plage = !$hors_plage ? "AND operations.plageop_id IS NOT NULL" : "";

  //$salles = CSalle::getSallesStats($salle_id, $bloc_id);
  $series = array();
  $serie  = array('data' => array());

  $query = "SELECT COUNT(operations.operation_id) AS total,
      COUNT(DISTINCT(operations.date)) AS nb_days,
      COUNT(DISTINCT(sallesbloc.salle_id)) AS nb_salles,
      DATE_FORMAT(operations.date, '%m/%Y') AS mois,
      DATE_FORMAT(operations.date, '%Y-%m-01') AS orderitem
    FROM operations
    LEFT JOIN sejour ON operations.sejour_id = sejour.sejour_id
    LEFT JOIN sallesbloc ON operations.salle_id = sallesbloc.salle_id
    LEFT JOIN plagesop ON operations.plageop_id = plagesop.plageop_id
    LEFT JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
    WHERE operations.annulee = '0'
    AND operations.date BETWEEN '$debut' AND '$fin'
    $where_hors_plage
    AND sejour.group_id = '" . CGroups::loadCurrent()->_id . "'
    AND sallesbloc.stats = '1'";

  if ($prat_id) {
    $query .= "\nAND operations.chir_id = '$prat_id'";
  }
  if ($discipline_id) {
    $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
  }
  if ($codeCCAM) {
    $query .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
  }

  if ($salle_id) {
    $query .= "\nAND sallesbloc.salle_id = '$salle_id'";
  }
  else {
    $query .= "\nAND sallesbloc.bloc_id " . CSQLDataSource::prepareIn(array_keys($blocs), $bloc_id);
  }

  $query .= "\nGROUP BY mois ORDER BY orderitem";

  $result = $prat->_spec->ds->loadlist($query);

  foreach ($ticks as $i => $tick) {
    $f = true;
    foreach ($result as $r) {
      if ($tick[1] == $r["mois"]) {
        $res = $r["total"] / ($r["nb_days"] * $r["nb_salles"]);
        //$nbjours = CMbDT::workDaysInMonth($r["orderitem"]);
        //$serie['data'][] = array($i, $r["total"]/($nbjours*count($salles)));
        $serie['data'][] = array($i, $res);
        //$serie['data'][] = array($i, $r["total"]/($r["nb_days"]*count($salles)));
        $f = false;
      }
    }
    if ($f) {
      $serie["data"][] = array(count($serie["data"]), 0);
    }
  }

  $series[] = $serie;

  // Set up the title for the graph
  $title    = "Patients / jour / salle active dans le mois";
  $subtitle = "Uniquement les jours d'activité";
  if ($prat_id) {
    $subtitle .= " - Dr $prat->_view";
  }
  if ($discipline_id) {
    $subtitle .= " - $discipline->_view";
  }
  if ($salle_id) {
    $subtitle .= " - $salle->nom";
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