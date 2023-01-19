<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
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

$ex_object_date_min        = CValue::getOrSession('ex_object_date_min', CMbDT::date('first day of last month'));
$ex_object_date_max        = CValue::getOrSession('ex_object_date_max', CMbDT::date('first day of this month'));
$ex_object_grouping        = CValue::getOrSession('ex_object_grouping', 'day');
$ex_object_limit_threshold = CValue::getOrSession('ex_object_limit_threshold');

// Seuil confondant
if ($ex_object_limit_threshold && ($ex_object_limit_threshold < 0 || $ex_object_limit_threshold > 100)) {
  $ex_object_limit_threshold = null;
}

CView::enforceSlave();

$group_id = CGroups::get()->_id;

$ds = CSQLDataSource::get('std');

switch ($ex_object_grouping) {
  case 'week':
    $tick_size         = 1;
    $mysql_date_format = '%x-%v';
    $php_date_format   = '%Y-%V';
    break;

  case 'month':
    $tick_size         = 1;
    $mysql_date_format = '%Y-%m';
    $php_date_format   = '%Y-%m';
    break;

  default:
  case 'day':
    $tick_size         = 2;
    $mysql_date_format = '%Y-%m-%d';
    $php_date_format   = '%Y-%m-%d';
}

$date     = CMbDT::transform('monday this week', $ex_object_date_min, $php_date_format);
$date_max = CMbDT::transform('+ 1 week', CMbDT::date('sunday this week', $ex_object_date_max), $php_date_format);

$dates = array();
switch ($ex_object_grouping) {
  case 'month':
    $date     = CMbDT::transform('first day of this month', $ex_object_date_min, $php_date_format);
    $date_max = CMbDT::date('+ 1 month', CMbDT::transform('last day of this month', $ex_object_date_max, $php_date_format));

    do {
      $dates[] = $date;
      $date    = CMbDT::transform('+ 1 month', $date, $php_date_format);
    } while ($date <= $date_max);
    break;

  case 'week':
    $date     = CMbDT::transform('monday this week', $ex_object_date_min, $php_date_format);
    $date_min = CMbDT::date('monday this week', $ex_object_date_min);
    $date_max = CMbDT::date('+ 2 weeks', $ex_object_date_max);

    do {
      $dates[]  = $date;
      $date     = CMbDT::transform('+ 1 week', $date_min, $php_date_format);
      $date_min = CMbDT::date('+ 1 week', $date_min);
    } while ($date_min <= $date_max);
    break;

  default:
  case 'day':
    $date     = $ex_object_date_min;
    $date_max = CMbDT::date('+ 1 day', $ex_object_date_max);

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

$where = array(
  'group_id' => $ds->prepare('= ? OR group_id IS NULL', $group_id),
  'level'    => "= 'object'",
  $ds->prepare('datetime_create >= ?', "$ex_object_date_min 00:00:00"),
  $ds->prepare('datetime_create <= ?', "$ex_object_date_max 23:59:59"),
);

// Utilisation des formulaires (par cible)
$request = new CRequest(false);
$request->addSelect("COUNT(*) AS count, DATE_FORMAT(datetime_create, '$mysql_date_format') AS date, object_class");
$request->addTable('ex_link');
$request->addWhere($where);
$request->addGroup("DATE_FORMAT(datetime_create, '$mysql_date_format'), object_class");

$results_by_object_class = $ds->loadList($request->makeSelect());

$target_series = array(
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
      'barWidth'  => 0.9,
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

$form_series = array(
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
      'barWidth'  => 0.9,
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

// Agencement des résultats par cible
$target_total       = array();
$results_by_context = array();
foreach ($results_by_object_class as $_result) {
  $_label      = $_result['object_class'];
  $_date       = $_result['date'];
  $_count      = $_result['count'];
  $_date_index = $dates[$_date];

  if (!isset($results_by_context[$_label])) {
    $results_by_context[$_label] = array();

    foreach ($dates as $_index) {
      $results_by_context[$_label][$_index] = array($_index, 0);
    }
  }

  $results_by_context[$_label][$_date_index] = array(
    $_date_index,
    $_count,
  );

  if (!isset($target_total[$_date])) {
    $target_total[$_date] = 0;
  }

  $target_total[$_date] += $_count;
}

foreach ($results_by_context as $_label => $_results) {
  $target_series['series'][] = array(
    'label' => $_label,
    'data'  => $_results,
  );
}

// Utilisation des formulaires (par formulaire)
$request = new CRequest(false);
$request->addSelect("COUNT(*) AS count, DATE_FORMAT(datetime_create, '$mysql_date_format') AS date, ex_class_id");
$request->addTable('ex_link');
$request->addWhere($where);
$request->addGroup("DATE_FORMAT(datetime_create, '$mysql_date_format'), ex_class_id");

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

// Calcul du total par date
$form_total = array();
foreach ($results_by_ex_class as $_result) {
  $_date  = $_result['date'];
  $_count = $_result['count'];

  if (!isset($form_total[$_date])) {
    $form_total[$_date] = 0;
  }

  $form_total[$_date] += $_count;
}

// Création d'une série propre aux éléments à enlever
$threshold_series = array();
if ($ex_object_limit_threshold) {
  foreach ($form_total as $_date => $_total) {
    $_date_index = $dates[$_date];

    if (!$_total) {
      continue;
    }

    if (!isset($threshold_series[$_date_index])) {
      $threshold_series[$_date_index] = array($_date_index, 0);
    }

    foreach ($results_by_ex_class as $_key => $_result) {
      $_count = $_result['count'];

      if ($_result['date'] != $_date) {
        continue;
      }

      if (round(($_count / $_total) * 100) <= $ex_object_limit_threshold) {
        $threshold_series[$_date_index][1] += $_count;
        unset($results_by_ex_class[$_key]);
      }
    }
  }
}

// Agencement des résultats par formulaire
$results_by_form = array();
foreach ($results_by_ex_class as $_result) {
  $_ex_class_id = $_result['ex_class_id'];
  $_date        = $_result['date'];
  $_count       = $_result['count'];
  $_date_index  = $dates[$_date];

  if (!isset($results_by_form[$_ex_class_id])) {
    $results_by_form[$_ex_class_id] = array();

    foreach ($dates as $_index) {
      $results_by_form[$_ex_class_id][$_index] = array($_index, 0);
    }
  }

  $results_by_form[$_ex_class_id][$_date_index] = array($_date_index, $_count);
}

foreach ($results_by_form as $_ex_class_id => $_results) {
  $form_series['series'][] = array(
    'label' => $forms[$_ex_class_id],
    'data'  => $_results,
  );
}

// Placement de la série des éléments en deçà du seuil au début de la série portant sur l'utilisation des formulaires (par formulaire)
if ($ex_object_limit_threshold && $threshold_series) {
  $threshold = array(
    'label' => CAppUI::tr('common-Other|pl'),
    'data'  => $threshold_series,
    'color' => '#ccc',
  );

  array_unshift($form_series['series'], $threshold);
}

$series = array(
  'target' => $target_series,
  'forms'  => $form_series,
);

$total = array(
  'target' => $target_total,
  'forms'  => $form_total,
);

$smarty = new CSmartyDP();
$smarty->assign('series', $series);
$smarty->assign('total', $total);
$smarty->assign('dates', array_flip($dates));
$smarty->display('inc_ex_object_stats.tpl');