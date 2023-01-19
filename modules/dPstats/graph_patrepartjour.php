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
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récupération du graphique du nombre moyen de patient
 * en SSPI selon le jour de la semaine
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $bloc_id       Identifiant du bloc
 * @param null   $discipline_id Identifiant de la discipline
 * @param string $codeCCAM      Code CCAM
 *
 * @return array
 */
function graphPatRepartJour(
  $debut = null, $fin = null, $prat_id = 0, $bloc_id = 0,
  $func_id = 0, $discipline_id = null, $codeCCAM = ''
) {
  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $prat = new CMediusers();
  $prat->load($prat_id);

  $discipline = new CDiscipline;
  $discipline->load($discipline_id);

  $ticks = array(array("0", "Dimanche"),
    array("1", "Lundi"),
    array("2", "Mardi"),
    array("3", "Mercredi"),
    array("4", "Jeudi"),
    array("5", "Vendredi"),
    array("6", "Samedi"));

  $bloc  = new CBlocOperatoire();
  $blocs = $bloc->loadGroupList();
  if ($bloc_id) {
    $bloc->load($bloc_id);
  }

  $series = array();
  $serie  = array("data" => array());

  // Nombre de patients par jour de la semaine
  $query = "SELECT COUNT(operations.operation_id) AS total,
    COUNT(DISTINCT(operations.date)) AS nb_days,
    DATE_FORMAT(operations.date, '%W') AS jour,
    DATE_FORMAT(operations.date, '%w') AS orderitem
    FROM operations
    INNER JOIN sallesbloc ON operations.salle_id = sallesbloc.salle_id
    LEFT JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
    WHERE sallesbloc.stats = '1'
    AND operations.date BETWEEN '$debut' AND '$fin'
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

  $query  .= "\nGROUP BY jour ORDER BY orderitem";
  $result = $prat->_spec->ds->loadlist($query);

  foreach ($ticks as $i => $tick) {
    $f = true;
    foreach ($result as $r) {
      if ($i == $r["orderitem"]) {
        $serie["data"][] = array($tick[0], $r["total"] / $r["nb_days"]);
        $f               = false;
      }
    }
    if ($f) {
      $serie["data"][] = array(count($serie["data"]), 0);
    }
  }

  $serie["label"] = "moyenne";

  $series[] = $serie;

  // Set up the title for the graph
  $title    = "Patients moyens / jour de la semaine";
  $subtitle = "Uniquement les jours d'activité";
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