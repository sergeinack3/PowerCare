<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récupération des statistiques du nombre d'interventions par jour
 * selon plusieurs filtres
 *
 * @param string $date          Date de début
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
function graphActiviteZoom(
  $date, $prat_id = 0, $salle_id = 0, $bloc_id = 0,
  $func_id = 0, $discipline_id = 0, $codes_ccam = '', $type_hospi = "", $hors_plage = true
) {
  if (!$date) {
    $date = CMbDT::transform("+0 DAY", CMbDT::date(), "%m/%Y");
  }

  $prat = new CMediusers;
  $prat->load($prat_id);

  $salle = new CSalle;
  $salle->load($salle_id);

  $discipline = new CDiscipline;
  $discipline->load($discipline_id);

  // Gestion de la date
  $debut = substr($date, 3, 7) . "-" . substr($date, 0, 2) . "-01";
  $fin   = CMbDT::date("+1 MONTH", $debut);
  $fin   = CMbDT::date("-1 DAY", $fin);
  $step  = "+1 DAY";

  // Tableaux des jours
  $ticks       = array();
  $ticks2      = array();
  $serie_total = array(
    'label'   => 'Total',
    'data'    => array(),
    'markers' => array('show' => true),
    'bars'    => array('show' => false)
  );
  for ($i = $debut; $i <= $fin; $i = CMbDT::date($step, $i)) {
    $ticks[]               = array(count($ticks), CMbDT::format($i, "%a %d"));
    $ticks2[]              = array(count($ticks), CMbDT::format($i, "%d"));
    $serie_total['data'][] = array(count($serie_total['data']), 0);
  }

  $salles = CSalle::getSallesStats($salle_id, $bloc_id);

  $series = array();
  $total  = 0;

  // Gestion du hors plage
  $where_hors_plage = !$hors_plage ? "AND operations.plageop_id IS NOT NULL" : "";
  $inner_hors_plage = !$hors_plage ? "INNER JOIN plagesop ON operations.plageop_id = plagesop.plageop_id" : "";

  foreach ($salles as $salle) {
    $serie = array(
      'data'  => array(),
      'label' => $salle->nom
    );
    $query = "SELECT COUNT(operations.operation_id) AS total,
      DATE_FORMAT(operations.date, '%d') AS jour,
      sallesbloc.nom AS nom
      FROM operations
      INNER JOIN sejour ON operations.sejour_id = sejour.sejour_id
      INNER JOIN sallesbloc ON operations.salle_id = sallesbloc.salle_id
      $inner_hors_plage
      INNER JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
      WHERE operations.date BETWEEN '$debut' AND '$fin'
      AND operations.annulee = '0'
      $where_hors_plage
      AND sallesbloc.salle_id = '$salle->_id'";

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

    if ($type_hospi) {
      $query .= "\nAND sejour.type = '$type_hospi'";
    }

    $query .= "\nGROUP BY jour ORDER BY jour";

    $result = $salle->_spec->ds->loadlist($query);

    foreach ($ticks2 as $i => $tick) {
      $f = true;
      foreach ($result as $r) {
        if ($tick[1] == $r["jour"]) {
          $serie["data"][]            = array($i, $r["total"]);
          $serie_total["data"][$i][1] += $r["total"];
          $total                      += $r["total"];
          $f                          = false;
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
    $title    = "Nombre d'anesthésie par salle - " . CMbDT::format($debut, "%m/%Y");
    $subtitle = "$total anesthésies";
  }
  else {
    $title    = "Nombre d'interventions par salle - " . CMbDT::format($debut, "%m/%Y");
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

  $options = CFlotrGraph::merge(
    "bars",
    array(
      'title'    => $title,
      'subtitle' => $subtitle,
      'xaxis'    => array('ticks' => $ticks),
      'yaxis'    => array('autoscaleMargin' => 5),
      'bars'     => array('stacked' => true, 'barWidth' => 0.8),
    )
  );

  return array('series' => $series, 'options' => $options);
}
