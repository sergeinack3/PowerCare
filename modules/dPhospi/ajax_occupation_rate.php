<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CService;

$services_ids = CValue::getOrSession("services_ids");
$date         = CMbDT::date(CValue::getOrSession('date'));

/** @var CPDOMySQLDataSource $ds */
$ds = CSQLDataSource::get('std');

/* Récupération du nombre de lits */
$query      = "SELECT COUNT(*) FROM `lit`
    LEFT JOIN `chambre` ON `lit`.`chambre_id` = `chambre`.`chambre_id`
    WHERE `chambre`.`service_id` " . $ds->prepareIn($services_ids) . ";";
$result     = $ds->fetchRow($ds->exec($query));
$total_lits = $result[0];

/* Récupération du nombre de lits disponibles */
$query            = "SELECT COUNT(*) FROM `lit`
    LEFT JOIN `chambre` ON `lit`.`chambre_id` = `chambre`.`chambre_id`
    WHERE `chambre`.`service_id` " . $ds->prepareIn($services_ids) . " AND `lit`.`annule` = '0'
    AND `chambre`.`annule` = '0';";
$result           = $ds->fetchRow($ds->exec($query));
$total_lits_dispo = $result[0];
$max              = $total_lits;

$ticks  = array();
$series = array(
  'total_lits_dispo' => array(
    'name'  => 'total_lits_dispo',
    'label' => 'Capacité ouverte',
    'yaxis' => 1,
    'data'  => array(),
    'lines' => array('show' => true,),
    'color' => '#FFA700'
  ),
  'total_lits'       => array(
    'name'  => 'total_lits',
    'label' => 'Capacité installée',
    'yaxis' => 1,
    'data'  => array(),
    'lines' => array('show' => true,),
    'color' => '#8803BB'
  ),
  'patients'         => array(
    'name'  => 'patients',
    'label' => 'Lits occupés',
    'data'  => array(),
    'bars'  => array(
      'show'     => true,
      'barWidth' => 0.9,
      'fill'     => true,
    ),
    'color' => '#204DF0'
  ),
  'patients_hallway' => array(
    'name'  => 'patients_hallway',
    'label' => 'Patients dans le couloir',
    'data'  => array(),
    'bars'  => array(
      'show'     => true,
      'barWidth' => 0.9,
      'fill'     => true,
    ),
    'color' => '#00A8F0'
  ),
  'entrants'         => array(
    'name'  => 'entrants',
    'label' => 'Patients entrants',
    'data'  => array(),
    'bars'  => array(
      'show'        => true,
      'barWidth'    => 0.45,
      'fill'        => true,
      'fillOpacity' => 0.6,
      'lineWidth'   => 1.5,
      'centered'    => false,
    ),
    'color' => '#066C16'
  ),
  'sortants'         => array(
    'name'  => 'sortants',
    'label' => 'Patients sortants',
    'data'  => array(),
    'bars'  => array(
      'show'        => true,
      'barWidth'    => 0.45,
      'fill'        => true,
      'fillOpacity' => 0.6,
      'lineWidth'   => 2,
      'centered'    => false,
    ),
    'color' => '#F00000'
  )
);

for ($h = 0; $h < 24; $h++) {
  $_hour                          = str_pad($h, 2, "0", STR_PAD_LEFT);
  $ticks[]                        = array($h, $_hour . 'h');
  $series['total_lits']['data'][] = array($h, $total_lits);

  /* Récupération du nombre de lits bloqués */
  $query           = "SELECT COUNT(DISTINCT `lit_id`) FROM `affectation`
      WHERE `service_id` " . $ds->prepareIn($services_ids) . " AND `entree` <= '$date $_hour:59:59'
      AND `sortie` >= '$date $_hour:00:00' AND `sejour_id` IS NULL;";
  $result          = $ds->fetchRow($ds->exec($query));
  $nb_blocked_beds = $result[0];

  $series['total_lits_dispo']['data'][] = array($h, $total_lits_dispo - $nb_blocked_beds);

  /* Récupération du nombre lits occupés */
  $query            = "SELECT COUNT(DISTINCT a.`lit_id`) FROM `affectation` AS a
      LEFT JOIN `sejour` ON (a.`sejour_id` = `sejour`.`sejour_id`)
      WHERE a.`service_id` " . $ds->prepareIn($services_ids) . " AND a.`entree` <= '$date $_hour:59:59'
      AND a.`sortie` >= '$date $_hour:00:00' AND a.`sejour_id` IS NOT NULL AND `sejour`.`annule` = '0';";
  $result           = $ds->fetchRow($ds->exec($query));
  $nb_occupied_beds = $result[0];

  /* Récupération du nombre de patients dans le couloir */
  $query              = "SELECT COUNT(*) FROM `affectation` AS a
      LEFT JOIN `sejour` ON (a.`sejour_id` = `sejour`.`sejour_id`)
      WHERE a.`service_id` " . $ds->prepareIn($services_ids) . " AND a.`entree` <= '$date $_hour:59:59'
      AND a.`sortie` >= '$date $_hour:00:00' AND a.`sejour_id` IS NOT NULL AND a.`lit_id` IS NULL
      AND `sejour`.`annule` = '0' AND a.`sejour_id`
      NOT IN (
        SELECT `sejour_id` FROM `affectation`
        WHERE `service_id` " . $ds->prepareIn($services_ids) . " AND `entree` <= '$date $_hour:59:59'
        AND `sortie` >= '$date $_hour:00:00' AND `sejour_id` IS NOT NULL AND `lit_id` IS NOT NULL
      );";
  $result             = $ds->fetchRow($ds->exec($query));
  $nb_patient_hallway = $result[0];

  if ($max < $nb_occupied_beds + $nb_patient_hallway) {
    $max = $nb_occupied_beds + $nb_patient_hallway;
  }

  $series['patients']['data'][]         = array($h, $nb_occupied_beds);
  $series['patients_hallway']['data'][] = array($h, $nb_occupied_beds + $nb_patient_hallway, $nb_patient_hallway);

  /* Récupération du nombre de patients entrants */
  $query                        = "SELECT COUNT(DISTINCT a.`sejour_id`) FROM `affectation` AS a
      LEFT JOIN `sejour` ON (a.`sejour_id` = `sejour`.`sejour_id`)
      WHERE a.`service_id` " . $ds->prepareIn($services_ids) . " AND a.`entree` <= '$date $_hour:59:59'
      AND a.`entree` >= '$date $_hour:00:00' AND a.`sejour_id` IS NOT NULL AND `sejour`.`annule` = '0' 
      AND a.`sejour_id` NOT IN (
        SELECT `sejour_id` FROM `affectation` as b
        WHERE `service_id` " . $ds->prepareIn($services_ids) . " AND `entree` <= '$date $_hour:59:59'
        AND `sortie` >= '$date $_hour:00:00' AND `sejour_id` IS NOT NULL AND a.`affectation_id` != b.`affectation_id`
      );";
  $result                       = $ds->fetchRow($ds->exec($query));
  $series['entrants']['data'][] = array($h - 0.45, $result[0]);

  /* Récupération du nombre de patients sortants */
  $query                        = "SELECT COUNT(DISTINCT a.`sejour_id`) FROM `affectation` AS a
      LEFT JOIN `sejour` ON (a.`sejour_id` = `sejour`.`sejour_id`)
      WHERE a.`service_id` " . $ds->prepareIn($services_ids) . " AND a.`sortie` <= '$date $_hour:59:59'
      AND a.`sortie` >= '$date $_hour:00:00' AND a.`sejour_id` IS NOT NULL AND `sejour`.`annule` = '0'
      AND a.`sejour_id` NOT IN (
        SELECT `sejour_id` FROM `affectation` as b
        WHERE `service_id` " . $ds->prepareIn($services_ids) . " AND `entree` <= '$date $_hour:59:59'
        AND `sortie` >= '$date $_hour:00:00' AND `sejour_id` IS NOT NULL AND a.`affectation_id` != b.`affectation_id`
      );";
  $result                       = $ds->fetchRow($ds->exec($query));
  $series['sortants']['data'][] = array($h, $result[0]);
}

$services_names = array();
foreach ($services_ids as $_service_id) {
  $service = new CService();
  $service->load($_service_id);
  $services_names[] = $service->nom;
}


$options = array(
  'title'            => 'Répartition du nombre de patients par heure, le ' . CMbDT::format($date, CAppUI::conf('date')),
  'subtitle'         => 'Services : ' . implode(', ', $services_names),
  'shadowSize'       => 0,
  'xaxis'            => array(
    'ticks' => $ticks,
    'title' => 'Heure',
  ),
  'yaxis'            => array(
    'title'        => 'Nombre de patients',
    'tickDecimals' => 0,
    'min'          => 0,
    'max'          => $max + 5,
  ),
  'legend'           => array(
    'show'     => true,
    'position' => 'nw'
  ),
  'grid'             => array('verticalLines' => false),
  'mouse'            => array(
    'track'          => true,
    'position'       => 'ne',
    'relative'       => true,
    'sensibility'    => 2,
    'trackFormatter' => "(
        function(obj) {
          var label = obj.series.label;

          if (obj.series.lines.show) {
            return printf('%s : %d', label, obj.y);
          }
          else {
            if (obj.series.name == 'patients') {
              var format = 'Taux d\'occupation : %s<br/>Taux de performances : %s<br/>Nombre de patients présents de %d' + 'h à %d' + 'h : %d';
              var occupation = Math.round(100 * obj.y / graph.options.total_lits) + '%';
              var performance = Math.round(100 * obj.y / graph.options.total_lits_dispo) + '%';
              var h = parseInt(obj.x);
              return printf(format, occupation, performance, h, h + 1, obj.y);
            }
            else {
              if (obj.series.name == 'patients_hallway') {
                var format = 'Nombre de patients présents<br />dans le couloir de %d' + 'h à %d' + 'h : %d';
                var h = parseInt(obj.x);
                console.debug(obj.series.data[h]);
                return printf(format, h, h + 1, obj.series.data[h][2]);
              }
              else {
                var format = '%s de %d' + 'h à %d' + 'h : %d';
                if (obj.series.name == 'entrants') {
                  var h = parseInt(round(obj.x));
                }
                else {
                  var h = parseInt(obj.x);
                }
                return printf(format, label, h, h + 1, obj.y);
              }
            }
          }
        }
      )"
  ),
  'total_lits'       => $total_lits,
  'total_lits_dispo' => $total_lits_dispo
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("graph", array('series' => array_values($series), 'options' => $options));

$smarty->display("inc_occupation_rate.tpl");
