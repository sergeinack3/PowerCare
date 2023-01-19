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
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClassField;

CCanDo::checkRead();

$param_min_count = CValue::getOrSession("param_min_count");

CView::enforceSlave();

$param_stats = array();

$group_id = CGroups::loadCurrent()->_id;

// Stats sur les champs
$ex_class_field = new CExClassField();

$where  = array(
  "ex_class.group_id" => "= '$group_id' OR ex_class.group_id IS NULL",
);
$ljoin  = array(
  "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
  "ex_class"             => "ex_class.ex_class_id = ex_class_field_group.ex_class_id",
);
$fields = array(
  "ex_class.ex_class_id",
  "ex_class.name",
);

// Total
$count_total = $ex_class_field->countMultipleList($where, "ex_class.name", "ex_class.ex_class_id", $ljoin, $fields);

if ($param_min_count) {
  foreach ($count_total as $_id => $_count) {
    if ($_count["total"] < $param_min_count) {
      unset($count_total[$_id]);
    }
  }

  $count_total = array_values($count_total);
}

// Désactivés
$where_disabled                            = $where;
$where_disabled["ex_class_field.disabled"] = "= '1'";

$count_disabled = $ex_class_field->countMultipleList($where_disabled, "ex_class.name", "ex_class.ex_class_id", $ljoin, $fields);
$count_disabled = array_combine(CMbArray::pluck($count_disabled, "ex_class_id"), $count_disabled);

// Reportés
$where_reported                                = $where;
$where_reported["ex_class_field.disabled"]     = "= '0'";
$where_reported["ex_class_field.report_class"] = "IS NOT NULL";

$count_reported = $ex_class_field->countMultipleList($where_reported, "ex_class.name", "ex_class.ex_class_id", $ljoin, $fields);
$count_reported = array_combine(CMbArray::pluck($count_reported, "ex_class_id"), $count_reported);

$ticks  = array();
$totals = array();

$options = array(
  "bars"  => array(
    "show"     => true,
    "barWidth" => 0.8,
  ),
  "xaxis" => array(
    "show" => false,
  ),
  "grid"  => array(
    "hoverable" => true,
  ),
);

$series = array(
  // Total
  array(
    "label" => CAppUI::tr("CExClassField-msg-not_disabled_not_reported"),
    "data"  => array(),
    "stack" => true,
  ),

  // Désactivés
  array(
    "label" => CAppUI::tr("CExClassField-msg-disabled"),
    "data"  => array(),
    "stack" => true,
  ),

  // Avec report de valeur
  array(
    "label" => CAppUI::tr("CExClassField-msg-reported"),
    "data"  => array(),
    "stack" => true,
  ),
);

foreach ($count_total as $_i => $_data) {
  $_total      = $_data["total"];
  $totals[$_i] = $_total;

  $_ex_class_id = $_data["ex_class_id"];

  $ticks[$_i] = $_data["name"];

  if (isset($count_disabled[$_ex_class_id])) {
    $_count              = $count_disabled[$_ex_class_id]["total"];
    $_total              -= $_count;
    $series[1]["data"][] = array($_i, $_count);
  }

  if (isset($count_reported[$_ex_class_id])) {
    $_count              = $count_reported[$_ex_class_id]["total"];
    $_total              -= $_count;
    $series[2]["data"][] = array($_i, $_count);
  }

  $series[0]["data"][] = array($_i, $_total);
}

// sort totals by total
usort(
  $count_total,
  function ($a, $b) {
    return $b["total"] - $a["total"];
  }
);

$smarty = new CSmartyDP();
$smarty->assign("param_min_count", $param_min_count);
$smarty->assign("param_options", $options);
$smarty->assign("param_series", $series);
$smarty->assign("param_ticks", $ticks);
$smarty->assign("param_totals", $totals);
$smarty->assign("param_forms_total", $count_total);
$smarty->assign("param_forms_disabled", $count_disabled);
$smarty->assign("param_forms_reported", $count_reported);
$smarty->display('inc_ex_class_stats.tpl');