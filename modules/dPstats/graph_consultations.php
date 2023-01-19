<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $m, $debutact, $finact, $prat_id;

use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récupération des statistiques du nombre de consultations par mois
 * selon plusieurs filtres
 *
 * @param string $debut   Date de début
 * @param string $fin     Date de fin
 * @param int    $prat_id Identifiant du praticien
 *
 * @return array
 */
function graphConsultations($debut = null, $fin = null, $prat_id = 0) {
  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $rectif   = CMbDT::transform("+0 DAY", $debut, "%d") - 1;
  $debutact = CMbDT::date("-$rectif DAYS", $debut);

  $rectif = CMbDT::transform("+0 DAY", $fin, "%d") - 1;
  $finact = CMbDT::date("-$rectif DAYS", $fin);
  $finact = CMbDT::date("+ 1 MONTH", $finact);
  $finact = CMbDT::date("-1 DAY", $finact);

  $pratSel = new CMediusers;
  $pratSel->load($prat_id);

  $ticks       = array();
  $serie_total = array(
    'label'   => 'Total',
    'data'    => array(),
    'markers' => array('show' => true),
    'bars'    => array('show' => false)
  );
  for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 MONTH", $i)) {
    $ticks[]               = array(count($ticks), CMbDT::transform("+0 DAY", $i, "%m/%Y"));
    $serie_total['data'][] = array(count($serie_total['data']), 0);
  }

  $ds     = CSQLDataSource::get("std");
  $total  = 0;
  $series = array();

  $query = "SELECT COUNT(consultation.consultation_id) AS total,
    DATE_FORMAT(plageconsult.date, '%m/%Y') AS mois,
    DATE_FORMAT(plageconsult.date, '%Y%m') AS orderitem
    FROM consultation
    INNER JOIN plageconsult
    ON consultation.plageconsult_id = plageconsult.plageconsult_id
    INNER JOIN users_mediboard
    ON plageconsult.chir_id = users_mediboard.user_id
    WHERE plageconsult.date BETWEEN '$debutact' AND '$finact'
    AND consultation.annule = '0'";

  if ($prat_id) {
    $query .= "\nAND plageconsult.chir_id = '$prat_id'";
  }
  $query .= "\nGROUP BY mois ORDER BY orderitem";

  $serie = array(
    'data' => array()
  );

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

  // Set up the title for the graph
  $title    = "Nombre de consultations";
  $subtitle = "- $total consultations -";

  if ($prat_id) {
    $subtitle .= " Dr $pratSel->_view -";
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
