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
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Récupération du graphique d'occupation des ressources au bloc opératoire
 * (personnel, vacations attribuées, ouverture de salle)
 *
 * @param string $debut         Date de début
 * @param string $fin           Date de fin
 * @param int    $prat_id       Identifiant du praticien
 * @param int    $salle_id      Identifiant de la salle
 * @param int    $bloc_id       Identifiant du bloc
 * @param null   $discipline_id Identifiant de la discipline
 * @param string $codeCCAM      Code CCAM
 * @param string $type_hospi    Type d'hospitalisation
 * @param bool   $hors_plage    Pris en compte des hors plage
 * @param string $type_duree    Type de durée analysée (jours / mois)
 *
 * @return array
 */
function graphTempsSalle(
  $debut = null, $fin = null, $prat_id = 0, $salle_id = 0, $bloc_id = 0, $func_id = 0, $discipline_id = null,
  $codeCCAM = "", $type_hospi = "", $hors_plage = true, $type_duree = "MONTH"
) {

  $ds = CSQLDataSource::get("std");

  if ($type_duree == "MONTH") {
    $type_duree_fr = "mois";
    $date_format   = "%m/%Y";
    $order_key     = "%Y%m";
  }
  else {
    $type_duree_fr = "jour";
    $date_format   = CAppUI::conf("date");
    $order_key     = "%Y%m%d";
  }

  if (!$debut) {
    $debut = CMbDT::date("-1 YEAR");
  }
  if (!$fin) {
    $fin = CMbDT::date();
  }

  $prat = new CMediusers;
  $prat->load($prat_id);

  $salle = new CSalle();
  $salle->load($salle_id);

  $bloc = new CBlocOperatoire();
  $bloc->load($bloc_id);

  $discipline = new CDiscipline;
  $discipline->load($discipline_id);

  $ticks = array();
  for ($i = $debut; $i <= $fin; $i = CMbDT::date("+1 $type_duree", $i)) {
    $ticks[] = array(count($ticks), CMbDT::transform("+0 DAY", $i, $date_format));
  }

  $salles = CSalle::getSallesStats($salle_id, $bloc_id);

  $seriesTot = array();
  $totalTot  = 0;

  // First serie : occupation du personnel
  $serieTot = array(
    'data'  => array(),
    'label' => "Occupation du personnel"
  );

  $query = "SELECT SUM(TIME_TO_SEC(affectation_personnel.fin) - TIME_TO_SEC(affectation_personnel.debut)) as total,
    DATE_FORMAT(plagesop.date, '$date_format') AS $type_duree_fr,
    DATE_FORMAT(plagesop.date, '$order_key') AS orderitem
    FROM plagesop
    LEFT JOIN operations ON plagesop.plageop_id = operations.plageop_id
    LEFT JOIN users_mediboard ON plagesop.chir_id = users_mediboard.user_id
    LEFT JOIN affectation_personnel ON operations.operation_id = affectation_personnel.object_id ";

  if ($type_hospi) {
    $query .= "LEFT JOIN sejour ON sejour.sejour_id = operations.sejour_id ";
  }

  $query .= "WHERE affectation_personnel.debut < affectation_personnel.fin
    AND affectation_personnel.debut IS NOT NULL
    AND affectation_personnel.fin IS NOT NULL
    AND affectation_personnel.object_class = 'COperation'
    AND plagesop.salle_id " . CSQLDataSource::prepareIn(array_keys($salles));

  if ($codeCCAM) {
    $query .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
  }
  if ($type_hospi) {
    $query .= "\nAND sejour.type = '$type_hospi'";
  }
  if ($prat_id) {
    $query .= "\nAND plagesop.chir_id = '$prat_id'";
  }
  if ($discipline_id) {
    $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
  }
  $query .= "\nAND plagesop.date BETWEEN '$debut' AND '$fin'
    GROUP BY operations.plageop_id HAVING total > 0 ORDER BY orderitem";

  $result = $ds->loadlist($query);

  $result_hors_plage = array();
  if ($hors_plage) {
    $query_hors_plage = "SELECT SUM(TIME_TO_SEC(affectation_personnel.fin) - TIME_TO_SEC(affectation_personnel.debut)) as total,
      DATE_FORMAT(operations.date, '$date_format') AS $type_duree_fr,
      DATE_FORMAT(operations.date, '$order_key') AS orderitem
      FROM operations
      LEFT JOIN users_mediboard ON operations.chir_id = users_mediboard.user_id
      LEFT JOIN affectation_personnel ON operations.operation_id = affectation_personnel.object_id ";
    if ($type_hospi) {
      $query_hors_plage .= "LEFT JOIN sejour ON sejour.sejour_id = operations.sejour_id ";
    }

    $query_hors_plage .= "WHERE affectation_personnel.debut < affectation_personnel.fin
      AND operations.date IS NOT NULL
      AND operations.plageop_id IS NULL
      AND affectation_personnel.debut IS NOT NULL
      AND affectation_personnel.fin IS NOT NULL
      AND affectation_personnel.object_class = 'COperation'
      AND operations.salle_id " . CSQLDataSource::prepareIn(array_keys($salles));

    if ($type_hospi) {
      $query_hors_plage .= "\nAND sejour.type = '$type_hospi'";
    }
    if ($prat_id) {
      $query_hors_plage .= "\nAND operations.chir_id = '$prat_id'";
    }
    if ($discipline_id) {
      $query_hors_plage .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
    }
    if ($codeCCAM) {
      $query_hors_plage .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
    }

    $query_hors_plage .= "\nAND operations.date BETWEEN '$debut' AND '$fin'
      GROUP BY $type_duree_fr HAVING total > 0 ORDER BY orderitem";

    $result_hors_plage = $ds->loadlist($query_hors_plage);
  }

  $calcul_temp = array();
  foreach ($result as $r) {
    if (!isset($calcul_temp[$r[$type_duree_fr]])) {
      $calcul_temp[$r[$type_duree_fr]] = 0;
    }
    $calcul_temp[$r[$type_duree_fr]] += $r['total'];
  }

  foreach ($ticks as $i => $tick) {
    $f = true;
    foreach ($calcul_temp as $key => $r) {
      if ($tick[1] == $key) {
        if ($hors_plage) {
          foreach ($result_hors_plage as &$_r_h) {
            if ($tick[1] == $_r_h[$type_duree_fr]) {
              $r += $_r_h["total"];
              unset($_r_h);
              break;
            }
          }
        }
        $serieTot['data'][] = array($i, $r / (60 * 60));
        $totalTot           += $r / (60 * 60);
        $f                  = false;
      }
    }
    if ($f) {
      $serieTot["data"][] = array(count($serieTot["data"]), 0);
    }
  }

  $seriesTot[] = $serieTot;


  // Second serie : Ouverture de salle
  $serieTot = array(
    'data'  => array(),
    'label' => "Ouverture de salle"
  );

  $query = "SELECT MAX(TIME_TO_SEC(TIME(operations.sortie_salle))) - MIN(TIME_TO_SEC(TIME(operations.entree_salle))) as total,
    DATE_FORMAT(plagesop.date, '$date_format') AS $type_duree_fr,
    DATE_FORMAT(plagesop.date, '$order_key') AS orderitem
    FROM plagesop
    LEFT JOIN operations ON plagesop.plageop_id = operations.plageop_id
    LEFT JOIN users_mediboard ON plagesop.chir_id = users_mediboard.user_id ";

  if ($type_hospi) {
    $query .= "LEFT JOIN sejour ON sejour.sejour_id = operations.sejour_id ";
  }
  $query .= "WHERE operations.entree_salle < operations.sortie_salle
  AND plagesop.salle_id " . CSQLDataSource::prepareIn(array_keys($salles));

  if ($codeCCAM) {
    $query .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
  }
  if ($type_hospi) {
    $query .= "\nAND sejour.type = '$type_hospi'";
  }
  if ($prat_id) {
    $query .= "\nAND plagesop.chir_id = '$prat_id'";
  }
  if ($discipline_id) {
    $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
  }
  $query .= "\nAND plagesop.date BETWEEN '$debut' AND '$fin'
    GROUP BY operations.plageop_id ORDER BY orderitem";

  $result = $ds->loadlist($query);

  $calcul_temp = array();
  foreach ($result as $r) {
    if (!isset($calcul_temp[$r[$type_duree_fr]])) {
      $calcul_temp[$r[$type_duree_fr]] = 0;
    }
    $calcul_temp[$r[$type_duree_fr]] += $r['total'];
  }

  foreach ($ticks as $i => $tick) {
    $f = true;
    foreach ($calcul_temp as $key => $r) {
      if ($tick[1] == $key) {
        $serieTot['data'][] = array($i, $r / (60 * 60));
        $totalTot           += $r / (60 * 60);
        $f                  = false;
      }
    }
    if ($f) {
      $serieTot["data"][] = array(count($serieTot["data"]), 0);
    }
  }

  $seriesTot[] = $serieTot;

  // Third serie : reservé
  $serieTot = array(
    'data'  => array(),
    'label' => "Vacations attribuées"
  );
  $query    = "SELECT SUM(TIME_TO_SEC(plagesop.fin) - TIME_TO_SEC(plagesop.debut)) AS total,
    DATE_FORMAT(plagesop.date, '$date_format') AS $type_duree_fr,
    DATE_FORMAT(plagesop.date, '$order_key') AS orderitem
    FROM plagesop
    LEFT JOIN users_mediboard ON plagesop.chir_id = users_mediboard.user_id ";

  if ($type_hospi || $codeCCAM) {
    $query .= "LEFT JOIN operations ON operations.plageop_id = plagesop.plageop_id
      LEFT JOIN sejour ON sejour.sejour_id = operations.sejour_id ";
  }
  $query .= "WHERE plagesop.salle_id " . CSQLDataSource::prepareIn(array_keys($salles));

  if ($codeCCAM) {
    $query .= "\nAND operations.codes_ccam LIKE '%$codeCCAM%'";
  }
  if ($type_hospi) {
    $query .= "\nAND sejour.type = '$type_hospi'";
  }
  if ($prat_id) {
    $query .= "\nAND plagesop.chir_id = '$prat_id'";
  }
  if ($discipline_id) {
    $query .= "\nAND users_mediboard.discipline_id = '$discipline_id'";
  }
  $query  .= "\nAND plagesop.date BETWEEN '$debut' AND '$fin'
    GROUP BY $type_duree_fr ORDER BY orderitem";
  $result = $ds->loadList($query);

  foreach ($ticks as $i => $tick) {
    $f = true;
    foreach ($result as $r) {
      if ($tick[1] == $r[$type_duree_fr]) {
        $serieTot['data'][] = array($i, $r["total"] / (60 * 60));
        $totalTot           += $r["total"] / (60 * 60);
        $f                  = false;
      }
    }
    if ($f) {
      $serieTot["data"][] = array(count($serieTot["data"]), 0);
    }
  }

  $seriesTot[] = $serieTot;

  // Set up the title for the graph
  $subtitle = "";
  if ($prat_id) {
    $subtitle .= " - Dr $prat->_view";
  }
  if ($discipline_id) {
    $subtitle .= " - $discipline->_view";
  }
  if ($salle_id) {
    $subtitle .= " - $salle->nom";
  }
  if ($bloc_id) {
    $subtitle .= " - $bloc->nom";
  }
  if ($codeCCAM) {
    $subtitle .= " - CCAM : $codeCCAM";
  }
  if ($type_hospi) {
    $subtitle .= " - " . CAppUI::tr("CSejour.type.$type_hospi");
  }

  $optionsTot = CFlotrGraph::merge(
    "lines",
    array(
      'title'    => "Utilisation des ressources",
      'subtitle' => "total estimé $subtitle",
      'xaxis'    => array('ticks' => $ticks),
      'yaxis'    => array('autoscaleMargin' => 5),
      'grid'     => array('verticalLines' => true)
    )
  );
  if ($totalTot == 0) {
    $optionsTot['yaxis']['max'] = 1;
  }

  return array('series' => $seriesTot, 'options' => $optionsTot);
}