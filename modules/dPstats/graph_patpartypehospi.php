<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Récupération du graphique du nombre de patients hospitalisés
 * par type d'hospitalisation
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $service_id    Identifiant du service
 * @param int    $type_adm      Type d'admission
 * @param int    $discipline_id Identifiant de la discipline
 * @param int    $septique      Filtre sur le caractère septique
 * @param string $type_data     Type de données (prévues / réelles)
 * @param string $codes_ccam    Code CCAM
 *
 * @return array
 */
function graphPatParTypeHospi(
  $debut = null, $fin = null, $prat_id = 0, $service_id = 0, $type_adm = 0,
  $func_id = 0, $discipline_id = 0, $septique = 0, $type_data = "prevue", $codes_ccam = null
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

  $sejour     = new CSejour();
  $listHospis = array();
  foreach ($sejour->_specs["type"]->_locales as $key => $type) {
    if (
      (($key == "comp" || $key == "ambu") && $type_adm == 1) ||
      ($type_adm == $key) ||
      ($type_adm == null)
    ) {
      $listHospis[$key] = $type;
    }
  }

  $total  = 0;
  $series = array();
  foreach ($listHospis as $key => $type) {
    $serie = array(
      'label' => $type,
      'data'  => array()
    );

    $query = new CRequest();
    $query->addColumn('COUNT(DISTINCT sejour.sejour_id)', 'total');
    $query->addColumn('sejour.type');
    $query->addColumn("DATE_FORMAT(sejour.entree_$type_data, '%m/%Y')", 'mois');
    $query->addColumn("DATE_FORMAT(sejour.entree_$type_data, '%Y%m')", 'orderitem');
    $query->addTable('sejour');
    $query->addLJoinClause('users_mediboard', 'sejour.praticien_id = users_mediboard.user_id');
    $query->addLJoinClause('affectation', 'sejour.sejour_id = affectation.sejour_id');
    $query->addLJoinClause('service', 'affectation.service_id = service.service_id');
    $query->addWhereClause("sejour.entree_$type_data", "BETWEEN '$debut 00:00:00' AND '$fin 23:59:59'");
    $query->addWhereClause('sejour.group_id', "= '" . CGroups::loadCurrent()->_id . "'");
    $query->addWhereClause('sejour.type', " = '$key'");
    $query->addWhereClause('sejour.annule', "= '0'");

    if ($service_id) {
      $query->addWhereClause('service.service_id', "= '$service_id'");
    }
    if ($prat_id) {
      $query->addWhereClause('sejour.praticien_id', "= '$prat_id'");
    }
    if ($discipline_id) {
      $query->addWhereClause('users_mediboard.discipline_id', "= '$discipline_id'");
    }
    if ($septique) {
      $query->addWhereClause('sejour.septique', "= '$septique'");
    }

    if ($codes_ccam) {
      $query->addLJoinClause('operations', 'operations.sejour_id = sejour.sejour_id');
      $query->addWhere("sejour.codes_ccam LIKE '%$codes_ccam%' OR operations.codes_ccam LIKE '%$codes_ccam%'");
    }

    $query->addGroup('mois');
    $query->addOrder('orderitem');

    $result = $sejour->_spec->ds->loadlist($query->makeSelect());
    foreach ($ticks as $i => $tick) {
      $f = true;
      foreach ($result as $r) {
        if ($tick[1] == $r["mois"]) {
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

  $subtitle = "$total patients";
  if ($prat_id) {
    $subtitle .= " - Dr $prat->_view";
  }
  if ($discipline_id) {
    $subtitle .= " - $discipline->_view";
  }
  if ($septique) {
    $subtitle .= " - Septiques";
  }

  $options = array(
    'title'       => "Nombre d'admissions par type d'hospitalisation - $type_data",
    'subtitle'    => $subtitle,
    'xaxis'       => array('labelsAngle' => 45, 'ticks' => $ticks),
    'yaxis'       => array('min' => 0, 'autoscaleMargin' => 5),
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
