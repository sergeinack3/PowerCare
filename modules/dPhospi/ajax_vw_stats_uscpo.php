<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CFlotrGraph;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$operation    = new COperation();
$max_uscpo    = $operation->_specs["duree_uscpo"]->max;
$default_week = $operation->conf("default_week_stat_uscpo");

/** @var string $date_min */
/** @var string $date_max */
$date_min   = CView::get("date_min", "date default|" . CMbDT::date($default_week == "last" ? "-1 week" : null), true);
$date_max   = CView::get("date_max", "date default|" . CMbDT::date($default_week == "next" ? "+1 week" : null), true);
$service_id = CView::get("service_id", "ref class|CService", true);

CView::checkin();

if ($date_min > $date_max) {
  list($date_min, $date_max) = array($date_max, $date_min);
}

$operation = new COperation();

$where = array();
$ljoin = array();

$where["duree_uscpo"] = "> 0";
$where["annulee"]     = "!= '1'";
$where[]              = "operations.passage_uscpo = '1' or operations.passage_uscpo IS NULL";

if ($service_id) {
  $ljoin["sejour"]            = "sejour.sejour_id = operations.sejour_id";
  $where["sejour.service_id"] = "= '$service_id'";
}

$day    = $date_min;
$dates  = array();
$series = array();
$serie  = array(
  'data'  => array(),
  'label' => "Nombre de nuits prévues"
);

$today = CMbDT::date();

while ($day <= $date_max) {
  $display = CMbDT::dateToLocale($day);

  // On préfixe d'une étoile si c'est le jour courant
  if ($day == $today) {
    $display = "* " . $display;
  }

  $dates[]         = array(count($dates), $display);
  $day_min         = CMbDT::date("-$max_uscpo DAY", $day);
  $where[10]       = "operations.date BETWEEN '$day_min' AND '$day'";
  $where[11]       = "DATE_ADD(operations.date, INTERVAL duree_uscpo DAY) > '$day'";
  $count           = $operation->countList($where, null, $ljoin);
  $day             = CMbDT::date("+1 day", $day);
  $serie['data'][] = array(count($serie['data']) - 0.2, $count);
}


$series[] = $serie;
$day      = $date_min;
$serie    = array(
  'data'  => array(),
  'label' => "Nombre de nuits placées"
);

$ljoin["affectation"] = "affectation.sejour_id = operations.sejour_id";

while ($day <= $date_max) {
  $day_min         = CMbDT::date("-$max_uscpo DAY", $day);
  $where[10]       = "operations.date BETWEEN '$day_min' AND '$day'";
  $where[11]       = "DATE_ADD(operations.date, INTERVAL duree_uscpo DAY) > '$day'";
  $where[12]       = "DATE_ADD(operations.date, INTERVAL duree_uscpo DAY) <= affectation.sortie";
  $day             = CMbDT::date("+1 day", $day);
  $count           = $operation->countList($where, null, $ljoin);
  $serie['data'][] = array(count($serie['data']) + 0.2, intval($count));
}

$series[] = $serie;

$options = CFlotrGraph::merge(
  "bars",
  array(
    'title' => "Durées USCPO",
    'xaxis' => array('ticks' => $dates),
    'yaxis' => array('tickDecimals' => 0),
    'grid'  => array('verticalLines' => true),
    'bars'  => array('barWidth' => 0.4)
  )
);

$graph = array('series' => $series, 'options' => $options);

$group              = CGroups::loadCurrent();
$service            = new CService();
$where              = array();
$where["group_id"]  = "= '$group->_id'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $service->loadListWithPerms(PERM_READ, $where, $order);

$dates = array();
$day   = $date_min;

while ($day <= $date_max) {
  $dates[] = $day;
  $day     = CMbDT::date("+1 day", $day);
}

$smarty = new CSmartyDP;

$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("services", $services);
$smarty->assign("graph", $graph);
$smarty->assign("service_id", $service_id);
$smarty->assign("dates", $dates);

$smarty->display("inc_vw_stats_uscpo.tpl");
