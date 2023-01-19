<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkRead();

$ex_object_filling_date_max      = CValue::getOrSession('ex_object_filling_date_max', CMbDT::date());
$ex_object_filling_date_min      = CValue::getOrSession('ex_object_filling_date_min', CMbDT::date('monday this week', $ex_object_filling_date_max));
$ex_object_filling_grouping      = CValue::getOrSession('ex_object_filling_grouping', 'day');
$ex_object_filling_min_threshold = CValue::getOrSession('ex_object_filling_min_threshold');
$ex_object_filling_max_threshold = CValue::getOrSession('ex_object_filling_max_threshold');

CView::enforceSlave();

$group_id = CGroups::get()->_id;

$ds = CSQLDataSource::get('std');

switch ($ex_object_filling_grouping) {
  case 'week':
    $tick_size         = 1;
    $mysql_date_format = '%x-%v';
    $php_date_format   = '%Y-%V';
    break;

  default:
  case 'day':
    $tick_size         = 2;
    $mysql_date_format = '%Y-%m-%d';
    $php_date_format   = '%Y-%m-%d';
}

$date     = CMbDT::transform('monday this week', $ex_object_filling_date_min, $php_date_format);
$date_max = CMbDT::transform('+ 1 week', CMbDT::date('sunday this week', $ex_object_filling_date_max), $php_date_format);

$dates = array();
switch ($ex_object_filling_grouping) {
  case 'week':
    $date     = CMbDT::transform('monday this week', $ex_object_filling_date_min, $php_date_format);
    $date_min = CMbDT::date('monday this week', $ex_object_filling_date_min);
    $date_max = CMbDT::date('+ 2 weeks', $ex_object_filling_date_max);

    do {
      $dates[]  = $date;
      $date     = CMbDT::transform('+ 1 week', $date_min, $php_date_format);
      $date_min = CMbDT::date('+ 1 week', $date_min);
    } while ($date_min <= $date_max);
    break;

  default:
  case 'day':
    $date     = $ex_object_filling_date_min;
    $date_max = CMbDT::date('+ 1 day', $ex_object_filling_date_max);

    do {
      $dates[] = $date;
      $date    = CMbDT::transform('+ 1 day', $date, $php_date_format);
    } while ($date <= $date_max);
}

$ticks = array();
$i     = 0;
foreach ($dates as $_key => $_date) {
  $ticks[] = ($i % $tick_size == 0) ? array($_key, $_date) : array($_key, '');
  $i++;
}

$dates = array_flip($dates);
$min   = reset($dates);
$max   = end($dates);

$fields_series = array(
  'series'  => array(),
  'options' => array(
    'xaxis'  => array(
      'min'      => $min,
      'max'      => $max,
      'ticks'    => $ticks,
      'tickSize' => $tick_size,
    ),
    'yaxis'  => array(
      'tickDecimals' => 0,
    ),
    'bars'   => array(
      'show'      => true,
      'barWidth'  => 0.8,
      'lineWidth' => 0,
      'fill'      => 0.7,
    ),
    'series' => array(
      'stack' => true,
    ),
    'legend' => array(
      'show' => true,
    ),
    'grid'   => array(
      'hoverable' => true,
    ),
  ),
);

$where = array(
  'group_id' => $ds->prepare('= ? OR group_id IS NULL', $group_id),
  'level'    => "= 'object'",
  $ds->prepare('datetime_create >= ?', "$ex_object_filling_date_min 00:00:00"),
  $ds->prepare('datetime_create <= ?', "$ex_object_filling_date_max 23:59:59"),
);

// Récupération des formulaires utilisés
$request = new CRequest();
$request->addSelect("DISTINCT ex_class_id");
$request->addTable('ex_link');
$request->addWhere($where);

$results_by_ex_class = $ds->loadList($request->makeSelect());

// Récupération du nom des formulaires utilisés
$request = new CRequest();
$request->addSelect(array('ex_class_id', 'name'));
$request->addTable('ex_class');

$where = array(
  'group_id'    => $ds->prepare('= ? OR group_id IS NULL', $group_id),
  'ex_class_id' => $ds->prepareIn(CMbArray::pluck($results_by_ex_class, 'ex_class_id')),
);

$request->addWhere($where);
$forms = $ds->loadHashList($request->makeSelect());

// Récupération des formulaires utilisés pendant une période donnée, par date
$request = new CRequest(false);
$request->addSelect("DATE_FORMAT(datetime_create, '$mysql_date_format') AS date, ex_class_id");
$request->addTable('ex_link');

$where = array(
  'group_id' => $ds->prepare('= ? OR group_id IS NULL', $group_id),
  'level'    => "= 'object'",
  $ds->prepare('datetime_create >= ?', "$ex_object_filling_date_min 00:00:00"),
  $ds->prepare('datetime_create <= ?', "$ex_object_filling_date_max 23:59:59"),
);

$request->addWhere($where);
$request->addGroup("DATE_FORMAT(datetime_create, '$mysql_date_format'), ex_class_id");

$ex_links = $ds->loadList($request->makeSelect());

// Récupération du nombre de champs par formulaire
$fields_by_ex_class = array();
$ex_class_ids       = array_unique(CMbArray::pluck($ex_links, 'ex_class_id'));
foreach ($ex_class_ids as $_ex_class_id) {
  $request = new CRequest();
  $request->addSelect('f.name');
  $request->addTable(array('ex_class_field_group AS fg, ex_class_field AS f'));

  $where = array(
    'fg.ex_class_id'             => $ds->prepare('= ?', $_ex_class_id),
    'fg.ex_class_field_group_id' => '= f.ex_group_id',
    'f.disabled'                 => "= '0'",
  );

  $request->addWhere($where);

  $fields_by_ex_class[$_ex_class_id] = $ds->loadColumn($request->makeSelect());
}

// Récupération des instances de formulaire créées pendant une période donnée, par date
$request = new CRequest();
$request->addSelect("DATE_FORMAT(datetime_create, '$mysql_date_format') AS date, ex_class_id, ex_object_id");
$request->addTable('ex_link');

$where = array(
  'group_id' => $ds->prepare('= ? OR group_id IS NULL', $group_id),
  'level'    => "= 'object'",
  $ds->prepare('datetime_create >= ?', "$ex_object_filling_date_min 00:00:00"),
  $ds->prepare('datetime_create <= ?', "$ex_object_filling_date_max 23:59:59"),
);

$request->addWhere($where);

$ex_objects = $ds->loadList($request->makeSelect());

// Récupération des champs valués
$fields_total  = array();
$valued_fields = array();
foreach ($ex_objects as $_ex_object) {
  $_date         = $_ex_object['date'];
  $_ex_class_id  = $_ex_object['ex_class_id'];
  $_ex_object_id = $_ex_object['ex_object_id'];

  $select = array();
  foreach ($fields_by_ex_class[$_ex_class_id] as $_field) {
    $select[] = "({$_field} IS NULL)";
  }

  $where = array(
    'ex_object_id' => $ds->prepare('= ?', $_ex_object_id),
  );

  $request = new CRequest();
  $request->addSelect(implode(', ', $select));
  $request->addTable("ex_object_{$_ex_class_id}");
  $request->addWhere($where);

  if (!isset($valued_fields[$_ex_class_id])) {
    $valued_fields[$_ex_class_id] = array();
  }

  if (!isset($valued_fields[$_ex_class_id][$_date])) {
    $valued_fields[$_ex_class_id][$_date] = array();
  }


  $valued_fields[$_ex_class_id][$_date][] = array(
    'total'  => count($fields_by_ex_class[$_ex_class_id]),
    'fields' => count(
      array_filter(
        $ds->loadHash($request->makeSelect()),
        function ($v) {
          return $v == 0;
        }
      )
    ),
  );
}

// Taux de non-remplissage des formulaires
$fields_by_form = array();
foreach ($valued_fields as $_ex_class_id => $_results_by_date) {
  foreach ($_results_by_date as $_date => $_results) {
    // Somme plutôt que moyenne de champs non valués, pour mettre en exerque la taille du formulaire dans le graphique
    $value = array_sum(CMbArray::pluck($_results, 'fields'));

    if ($ex_object_filling_min_threshold && ($value < $ex_object_filling_min_threshold)) {
      continue;
    }
    elseif ($ex_object_filling_max_threshold && ($value > $ex_object_filling_max_threshold)) {
      continue;
    }

    $_date_index = $dates[$_date];

    if (!isset($fields_by_form[$_ex_class_id])) {
      $fields_by_form[$_ex_class_id] = array();

      foreach ($dates as $_index) {
        $fields_by_form[$_ex_class_id][$_index] = array($_index, 0);
      }
    }

    $fields_by_form[$_ex_class_id][$_date_index] = array($_date_index, $value);
  }
}

foreach ($fields_by_form as $_ex_class_id => $_results) {
  $fields_series['series'][] = array(
    'label' => $forms[$_ex_class_id],
    'data'  => $_results,
  );
}

$series = array(
  'fields' => $fields_series,
);

$smarty = new CSmartyDP();
$smarty->assign('series', $series);
$smarty->assign('dates', array_flip($dates));
$smarty->display('inc_ex_object_filling_stats.tpl');