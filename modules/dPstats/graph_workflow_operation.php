<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperationWorkflow;

/**
 * Récuparation du graphique du nombre d'interventions annulées le jour même
 *
 * @param string $date_min      Date de début
 * @param string $date_max      Date de fin
 * @param int    $prat_id       Filtre du praticien
 * @param int    $salle_id      Filtre de la salle
 * @param int    $bloc_id       Filtre du bloc
 * @param int    $func_id       Filtre sur un cabinet
 * @param int    $discipline_id Filtre sur une discipline
 * @param string $code_ccam     Code CCAM
 * @param string $type_sejour   Type de séjour
 * @param bool   $hors_plage    Prise en charge des hors plage
 *
 * @return array
 */
function graphWorkflowOperation(
  $date_min = null, $date_max = null, $prat_id = null, $salle_id = null, $bloc_id = null,
  $func_id = null, $discipline_id = null, $code_ccam = null, $type_sejour = null, $hors_plage = false
) {
  $miner = new COperationWorkflow();
  $miner->warnUsage();

  if (!$date_min) {
    $date_min = CMbDT::date("-1 YEAR");
  }

  if (!$date_max) {
    $date_max = CMbDT::date();
  }

  $date_min = CMbDT::format($date_min, "%Y-%m-01");
  $date_max = CMbDT::transform("+1 MONTH", $date_max, "%Y-%m-01");

  // Series declarations
  $labels = array(
    "op_count"                => "Nombre d'interventions",
    "creation"                => "Planification intervention",
    "consult_chir"            => "Consultation chirurgicale",
    "consult_anesth"          => "Consultation préanesthésique",
    "visite_anesth"           => "Visite anesthésiste",
    "creation_consult_chir"   => "RDV de consultation chirurgicale",
    "creation_consult_anesth" => "RDV de consultation préanesthésique",
  );

  $salles = CSalle::getSallesStats($salle_id, $bloc_id);

  $query = new CRequest();
  $query->addColumn("DATE_FORMAT(date_operation, '%Y-%m')", "mois");
  $query->addColumn("COUNT(operations.operation_id)", "op_count");

  // Prévention des données négatives aberrantes
  $tolerance_in_days = 0;
  $columns           = array(
    "creation",
    "consult_chir",
    "consult_anesth",
    "visite_anesth",
    "creation_consult_chir",
    "creation_consult_anesth",
  );

  foreach ($columns as $_column) {
    $field = "date_$_column";
    $diff  = "DATEDIFF(ow.date_operation, ow.$field)";
    $query->addColumn("AVG  (IF($diff > $tolerance_in_days, $diff, NULL))", $_column);
    $query->addColumn("COUNT(IF($diff > $tolerance_in_days, $diff, NULL))", "count_$_column");
  }

  $query->addTable("operations");
  $query->addLJoin("operation_workflow AS ow ON ow.operation_id = operations.operation_id");

  $query->addWhereClause("date_operation", "BETWEEN '$date_min' AND '$date_max'");
  $query->addWhereClause("salle_id", CSQLDataSource::prepareIn(array_keys($salles)));
  $query->addGroup("mois");
  $query->addOrder("mois");

  $subtitle = "";

  // Filtre sur hors plage
  if (!$hors_plage) {
    $query->addWhereClause("plageop_id", "IS NOT NULL");
    $subtitle .= " - sans hors plage";
  }

  // Filtre sur le salle (pas besoin de clause supplémentaire)
  if ($salle_id) {
    $salle    = reset($salles);
    $subtitle .= " - $salle->_view";
  }
  // Filtre sur le praticien
  if ($prat_id) {
    $query->addWhereClause("operations.chir_id", "= '$prat_id'");
    $prat = new CMediusers;
    $prat->load($prat_id);
    $subtitle .= " - Dr $prat->_view";
  }

  // Filtre sur le cabinet
  if ($func_id) {
    $query->addLJoinClause("users_mediboard", "operations.chir_id = users_mediboard.user_id");
    $query->addWhereClause("users_mediboard.function_id", "= '$func_id'");
    $func = new CFunctions;
    $func->load($func_id);
    $subtitle .= " - $func->_view";
  }

  // Filtre sur la discipline
  if ($discipline_id) {
    $discipline = new CDiscipline;
    $discipline->load($discipline_id);
    $query->addLJoinClause("users_mediboard", "operations.chir_id = users_mediboard.user_id");
    $query->addWhereClause("users_mediboard.discipline_id", "= '$discipline_id'");
    $subtitle .= " - $discipline->_view";
  }

  // Filtre sur les codes CCAM
  if ($code_ccam) {
    $query->addWhereClause("operations.codes_ccam", "LIKE '%$code_ccam%'");
    $subtitle .= " - CCAM: $code_ccam";
  }

  // Filtre sur le type d'hospitalisation
  if ($type_sejour) {
    $query->addLJoinClause("sejour", "sejour.sejour_id = operations.sejour_id");
    $query->addWhereClause("sejour.type", "= '$type_sejour'");
    $subtitle .= " - " . CAppUI::tr("CSejour.type.$type_sejour");
  }

  // Query result
  $ds         = CSQLDataSource::get("std");
  $all_values = $ds->loadHashAssoc($query->makeSelect());

  // Build horizontal ticks
  $months = array();
  $ticks  = array();
  for ($_date = $date_min; $_date < $date_max; $_date = CMbDT::date("+1 MONTH", $_date)) {
    $count_ticks                            = count($ticks);
    $ticks[]                                = array($count_ticks, CMbDT::format($_date, "%m/%Y"));
    $months[CMbDT::format($_date, "%Y-%m")] = $count_ticks;
  }

  // Series building
  $series = array();
  foreach ($labels as $_label_name => $_label_title) {
    $series[$_label_name] = array(
      "label" => $_label_title,
      "data"  => array(),
      "yaxis" => 2
    );
  }

  $series["op_count"]["markers"]["show"]   = true;
  $series["op_count"]["yaxis"]             = 1;
  $series["op_count"]["lines"]["show"]     = false;
  $series["op_count"]["points"]["show"]    = false;
  $series["op_count"]["bars"]["show"]      = true;
  $series["op_count"]["bars"]["fillColor"] = "#ccc";
  $series["op_count"]["color"]             = "#888";

  $total  = 0;
  $counts = array();
  foreach ($months as $_month => $_tick) {
    $values = isset($all_values[$_month]) ? $all_values[$_month] : array_fill_keys(array_keys($labels), null);
    unset($values["mois"]);

    $_counts = array();
    foreach ($values as $_name => $_value) {
      $parts = explode("_", $_name, 2);
      if ($parts[0] == "count") {
        $_counts[$labels[$parts[1]]] = $_value;
        continue;
      }

      $series[$_name]["data"][] = array($_tick, $_value);
    }

    $total    += $values["op_count"];
    $counts[] = $_counts;
  }

  // Set up the title for the graph
  $title    = "Anticipation de la programmation des interventions";
  $subtitle = "$total interventions" . $subtitle;

  $options = array(
    'title'    => $title,
    'subtitle' => $subtitle,
    'xaxis'    => array('labelsAngle' => 45, 'ticks' => $ticks),
    'yaxis'    => array('autoscaleMargin' => 5, "title" => "Quantité d'interventions", "titleAngle" => 90),
    'y2axis'   => array('autoscaleMargin' => 5, "title" => "Anticipation moyenne en jours vs la date d'intervention", "titleAngle" => 90),
    "points"   => array("show" => true, "radius" => 2, "lineWidth" => 1),
    "lines"    => array("show" => true, "lineWidth" => 1),
    'bars'     => array('show' => false, 'stacked' => false, 'barWidth' => 0.8),
    'HtmlText' => false,
    'legend'   => array('show' => true, 'position' => 'nw'),
    'grid'     => array('verticalLines' => false),
    'mouse'    => array(
      "track"          => true,
      "position"       => "ne",
      "relative"       => true,
      "sensibility"    => 2,
      "trackDecimals"  => 3,
      // Keep parenthesis wrapping to allow JS closure evaluation
      "trackFormatter" => "(
        function(obj) {
          var label = obj.series.label;
          var total = obj.nearest.allSeries[0].data[obj.index][1];
          var date = graph.options.xaxis.ticks[obj.index][1];

          // Barre des nombres d'interventions
          if (obj.series.bars.show) {
            var format = '%s <br />%s en %s';
            return printf(format, label, total, date);
          }

          // Courbes d'anticipation
          var count = graph.options.counts[obj.index][label];
          var value = obj.series.data[obj.index][1];
          var percent = Math.round(100*count/total) + '%';
          var format = '%s <br />%d jours en %s<br />%s des interventions concernées (%s/%s)';
          return printf(format, label, value, date, percent, count, total);
        }
      )"
    ),
    'counts'   => $counts,

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

  return array('series' => array_values($series), 'options' => $options);
}


