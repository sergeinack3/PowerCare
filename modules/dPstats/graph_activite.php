<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récupération des statistiques du nombre d'interventions par mois
 * selon plusieurs filtres
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $salle_id      Identifiant de la sall
 * @param int    $bloc_id       Identifiant du bloc
 * @param int    $discipline_id Identifiant de la discipline
 * @param string $codes_ccam    Code CCAM
 * @param string $type_hospi    Type d'hospitalisation
 * @param bool   $hors_plage    Prise en compte des hors plage
 *
 * @return array
 */
function graphActivite(
  $debut = null, $fin = null, $prat_id = 0, $salle_id = 0, $bloc_id = 0,
  $func_id = 0, $discipline_id = 0, $codes_ccam = "", $type_hospi = "", $hors_plage = true
) {
  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $prat = new CMediusers;
  $prat->load($prat_id);

  $discipline = new CDiscipline;
  $discipline->load($discipline_id);

  $salle = new CSalle;
  $salle->load($salle_id);

  $ticks       = array();
  $serie_total = array(
    'label'   => 'Total',
    'data'    => array(),
    'markers' => array('show' => true),
    'bars'    => array('show' => false),
  );
  for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 MONTH", $i)) {
    $ticks[]               = array(count($ticks), CMbDT::transform("+0 DAY", $i, "%m/%Y"));
    $serie_total['data'][] = array(count($serie_total['data']), 0);
  }

  $salles = CSalle::getSallesStats($salle_id, $bloc_id);
  $ds     = $salle->_spec->ds;

  // Gestion du hors plage
  $where_hors_plage = !$hors_plage ? "AND operations.plageop_id IS NOT NULL" : "";

  $total  = 0;
  $series = array();
  foreach ($salles as $salle) {
    $serie = array(
      'label' => $bloc_id ? $salle->nom : $salle->_view,
      'data'  => array()
    );

    $query = "SELECT COUNT(operations.operation_id) AS total,
      DATE_FORMAT(operations.date, '%m/%Y') AS mois,
      DATE_FORMAT(operations.date, '%Y%m') AS orderitem,
      sallesbloc.nom AS nom
      FROM operations
      LEFT JOIN sejour ON operations.sejour_id = sejour.sejour_id
      LEFT JOIN sallesbloc ON operations.salle_id = sallesbloc.salle_id
      LEFT JOIN plagesop ON operations.plageop_id = plagesop.plageop_id
      LEFT JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
      WHERE operations.annulee = '0'
      AND operations.date BETWEEN '$debut' AND '$fin'
      $where_hors_plage
      AND sejour.group_id = '" . CGroups::loadCurrent()->_id . "'";

    if ($type_hospi) {
      $query .= "\nAND sejour.type = '$type_hospi'";
    }
    if ($prat_id && !$prat->isFromType(array("Anesthésiste"))) {
      $query .= "\nAND operations.chir_id = '$prat_id'";
    }
    if ($prat_id && $prat->isFromType(array("Anesthésiste"))) {
      $query .= "\nAND (operations.anesth_id = '$prat_id' OR
                       (plagesop.anesth_id = '$prat_id' AND (operations.anesth_id = '0' OR operations.anesth_id IS NULL)))";
    }
    if ($discipline_id) {
      $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
    }
    if ($codes_ccam) {
      $query .= "\nAND operations.codes_ccam LIKE '%$codes_ccam%'";
    }
    $query .= "\nAND sallesbloc.salle_id = '$salle->_id'";
    $query .= "\nGROUP BY mois ORDER BY orderitem";

    $result = $ds->loadlist($query);

    foreach ($ticks as $i => $tick) {
      $f = true;
      foreach ($result as $r) {
        if ($tick[1] == $r["mois"]) {
          $serie["data"][]            = array($i, $r["total"]);
          $serie_total["data"][$i][1] += $r["total"];
          $total                      += $r["total"];
          $f                          = false;
          break;
        }
      }
      if ($f) {
        $serie["data"][] = array(count($serie["data"]), 0);
      }
    }
    $series[] = $serie;
  }

  $series[] = $serie_total;

  // Set up the title for the graph
  if ($prat_id && $prat->isFromType(array("Anesthésiste"))) {
    $title    = "Nombre d'anesthésie par salle";
    $subtitle = "$total anesthésies";
  }
  else {
    $title    = "Nombre d'interventions par salle";
    $subtitle = "$total interventions";
  }

  if ($prat_id) {
    $subtitle .= " - Dr $prat->_view";
  }
  if ($discipline_id) {
    $subtitle .= " - $discipline->_view";
  }
  if ($codes_ccam) {
    $subtitle .= " - CCAM : $codes_ccam";
  }
  if ($type_hospi) {
    $subtitle .= " - " . CAppUI::tr("CSejour.type.$type_hospi");
  }

  $options = array(
    'title'       => $title,
    'subtitle'    => $subtitle,
    'xaxis'       => array('labelsAngle' => 45, 'ticks' => $ticks),
    'yaxis'       => array('autoscaleMargin' => 5),
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
