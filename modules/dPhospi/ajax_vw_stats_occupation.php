<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkRead();
$date_min     = CView::get("date_min", "date default|" . CMbDT::date("-1 month"), true);
$date_max     = CView::get("date_max", "date default|" . CMbDT::date(), true);
$service_id   = CView::get("service_id", "ref class|CService", true);
$spec         = array(
  "str",
  "default" => array("ouvert" => 1, "prevu" => 1, "affecte" => 1, "entree" => 1)
);
$display_stat = CView::get("display_stat", $spec, true);
CView::checkin();

$group              = CGroups::loadCurrent();
$service            = new CService();
$where              = array();
$where["group_id"]  = "= '$group->_id'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $service->loadListWithPerms(PERM_READ, $where, $order);

// Template avec échec
$smarty = new CSmartyDP;
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("type", "occupation");
$smarty->assign("service_id", $service_id);
$smarty->assign("services", $services);

if (!$service_id) {
  $smarty->display("inc_form_stats.tpl");
  CAppUI::stepMessage(UI_MSG_ALERT, "warning-hospi-stats-choose_service");

  return;
}

$ds        = CSQLDataSource::get("std");
$dates     = array();
$date_temp = $date_min;
$series    = array();

while ($date_temp <= $date_max) {
  $dates[]   = array(count($dates), CMbDT::dateToLocale($date_temp));
  $date_temp = CMbDT::date("+1 day", $date_temp);
}

// Table temporaraire de dates pour les jointures
$tab_name = CSQLDataSource::tempTableDates($date_min, $date_max);

// Nombre de lits totaux sur le service
$lit = new CLit();

$where = array();
$ljoin = array();

$ljoin["chambre"]        = "chambre.chambre_id = lit.chambre_id";
$where["service_id"]     = " = '$service_id'";
$where["lit.annule"]     = " = '0'";
$where["chambre.annule"] = " = '0'";

$nb_lits = $lit->countList($where, null, $ljoin);
if (!$nb_lits) {
  $smarty->display("inc_form_stats.tpl");
  CAppUI::stepMessage(UI_MSG_WARNING, "warning-hospi-stats-no_beds");

  return;
}

// Lits ouverts (non bloqués - non compris les blocages des urgence)
$serie = array(
  "data"    => array(),
  "label"   => "Disponibles",
  "markers" => array("show" => true)
);

// Sauvegarde des lits ouverts par date
$lits_ouverts_par_date = array();

foreach ($dates as $key => $_date) {
  $date      = CMbDT::dateFromLocale($_date[1]);
  $query     = "SELECT count(DISTINCT l.lit_id) as lits_ouverts
    FROM lit l
    JOIN affectation a ON a.lit_id = l.lit_id AND
    DATE_FORMAT(a.entree, '%Y-%m-%d') <= '$date' AND DATE_FORMAT(a.sortie, '%Y-%m-%d') >= '$date'
    AND a.sejour_id != 0
    LEFT JOIN chambre c ON c.chambre_id = l.chambre_id
    WHERE  c.service_id = '$service_id'";
  $lits_pris = $ds->loadResult($query);

  $serie['data'][]              =
    array(
      count($serie['data'])/* - 0.3*/,
      $nb_lits - $lits_pris,
      ($nb_lits - $lits_pris) / $nb_lits);
  $lits_ouverts_par_date[$date] = $nb_lits - $lits_pris;
}

// Pour les autres stats, on a besoin du nombre de lits ouverts,
// donc la calculer dans tous les cas

if (isset($display_stat["ouvert"])) {
  $series[] = $serie;
}

// Prévu (séjours)
// WHERE s.service_id = '$service_id' => le service_id est pas notNull (en config)

if (isset($display_stat["prevu"])) {
  $serie = array(
    "data"    => array(),
    "label"   => "Prévu",
    "markers" => array("show" => true)
  );

  foreach ($dates as $key => $_date) {
    $date        = CMbDT::dateFromLocale($_date[1]);
    $query       = "SELECT count(sejour_id) as nb_prevu
    FROM sejour
    WHERE entree <= '$date 00:00:00' AND sortie >= '$date 00:00:00'
    AND service_id = '$service_id'";
    $prevu_count = $ds->loadResult($query);

    $serie["data"][] =
      array(count($serie["data"])/* - 0.1*/,
        $prevu_count,
        $prevu_count / $nb_lits);
  }

  $series[] = $serie;
}

// Réel (affectations)
if (isset($display_stat["affecte"])) {
  $serie = array(
    "data"    => array(),
    "label"   => "Affectés",
    "markers" => array("show" => true)
  );

  foreach ($dates as $key => $_date) {
    $date       = CMbDT::dateFromLocale($_date[1]);
    $query      = "SELECT count(affectation_id) as nb_reel
    FROM affectation d
    WHERE entree <= '$date 00:00:00' AND sortie >= '$date 00:00:00'
    AND service_id = '$service_id'";
    $reel_count = $ds->loadResult($query);

    $serie["data"][] =
      array(count($serie['data'])/* + 0.1*/,
        $reel_count,
        $reel_count / $nb_lits);
  }

  $series[] = $serie;
}

// Entrées dans la journée (nb de placements sur tous les lits sur chaque journée)
// Ne pas compter les blocages

if (isset($display_stat["entree"])) {
  $query = "SELECT d.date, count(affectation_id) as entrees
    FROM $tab_name d
    LEFT JOIN affectation a ON
      DATE_FORMAT(a.entree, '%Y-%m-%d') <= d.date AND DATE_FORMAT(a.sortie, '%Y-%m-%d') >= d.date
      AND a.sejour_id != 0 AND a.service_id = '$service_id'
    GROUP BY d.date
    ORDER BY d.date";

  $entrees_journee = $ds->loadList($query);

  $serie = array(
    "data"    => array(),
    "label"   => "Entrées",
    "markers" => array("show" => true)
  );

  foreach ($entrees_journee as $_entrees_by_day) {
    $serie["data"][] =
      array(count($serie['data'])/* + 0.3*/,
        $_entrees_by_day["entrees"],
        $_entrees_by_day["entrees"] / $nb_lits);
  }
  $series[] = $serie;
}

$options = CFlotrGraph::merge("bars", array(
  "title" => "Occupation des lits",
  "xaxis" => array("ticks" => $dates),
  "yaxis" => array("min" => 0),
  "grid"  => array("verticalLines" => true),
  "bars"  => array("barWidth" => 0.15, "stacked" => false)
));

$graph = array("series" => $series, "options" => $options);

$smarty = new CSmartyDP();
$smarty->assign("date_min"    , $date_min);
$smarty->assign("date_max"    , $date_max);
$smarty->assign("services"    , $services);
$smarty->assign("graph"       , $graph);
$smarty->assign("service_id"  , $service_id);
$smarty->assign("display_stat", $display_stat);
$smarty->display("inc_vw_stats_occupation");

