<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

$dsn          = CView::get("dsn", "str");
$table        = CView::get("table", "str");
$tooltip      = CView::get("tooltip", "bool default|0");
$line_compare = CView::get("line_compare", "str");

$start = (int)CView::get("start", "num default|0");
$count = (int)CView::get("count", "num default|50", true);

$order_column = CView::get("order_column", "str", true);
$order_way    = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);

$where_column = CView::get("where_column", "str");
$where_value  = CView::get("where_value", "str");
$search       = CView::get("search", "bool default|0");

CView::checkin();

$ds = CSQLDataSource::get($dsn);

$table_info = CImportTools::getTableInfo($ds, $table);
$columns    = $table_info["columns"];
foreach ($columns as $_name => $_col) {
  if ($_col['Key'] != 'PRI') {
    continue;
  }
  $table_info['primary_key'] = $_name;
}

$mb_object = null;
if ($line_compare && $table_info['primary_key'] && $table_info['class']) {
  $id_sante400 = new CIdSante400();

  $where = array(
    "tag"          => $ds->prepareLike("$dsn%"),
    "object_class" => $ds->prepare("= ?", $table_info['class']),
    "id400"        => $ds->prepare("= ?", $line_compare),
  );

  if ($id_sante400->loadObject($where)) {
    $mb_object = $id_sante400->loadTargetObject();
    $mb_object->canDo();
    $mb_object->loadView();
  }
}

$orderby = "";
if ($order_column) {
  $order_column = preg_replace('/[^-_\w]/', "", $order_column);

  if (in_array($order_column, array_keys($columns))) {
    if (!in_array($order_way, array("ASC", "DESC"))) {
      $order_way = "ASC";
    }
    $orderby = "$order_column $order_way";
  }
}

$request = new CRequest();
$request->addTable($table);
$request->addSelect("*");
$request->setLimit("$start,$count");
if ($orderby) {
  $request->addOrder($orderby);
}
if ($where_column) {
  $where = array(
    $where_column => $ds->prepare("=?", $where_value)
  );
  $request->addWhere($where);
}

$rows = $ds->loadList($request->makeSelect());

$request->setLimit(null);
$request->order = null;
$total          = $ds->loadResult($request->makeSelectCount());

$counts = array(
  10, 50, 100, 200, 500, 1000, 5000
);

$hidden_columns = array_sum(CMbArray::pluck($columns, 'hide'));

$smarty = new CSmartyDP();
$smarty->assign("rows", $rows);
$smarty->assign("columns", $columns);
$smarty->assign("hidden_columns", $hidden_columns);
$smarty->assign("table_info", $table_info);
$smarty->assign("tooltip", $tooltip);
$smarty->assign("line_compare", $line_compare);
$smarty->assign("dsn", $dsn);
$smarty->assign("table", $table);
$smarty->assign("total", $total);
$smarty->assign("start", $start);
$smarty->assign("count", $count);
$smarty->assign("counts", $counts);
$smarty->assign("order_column", $order_column);
$smarty->assign("order_way", $order_way);
$smarty->assign("where_column", $where_column);
$smarty->assign("where_value", $where_value);
$smarty->assign("mb_object", $mb_object);
$smarty->display(($search) ? "inc_vw_pop_table_lines" : "inc_vw_table_data.tpl");