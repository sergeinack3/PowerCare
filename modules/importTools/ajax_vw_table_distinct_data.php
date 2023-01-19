<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;
use Ox\Core\CMbArray;

CCanDo::checkAdmin();

$dsn    = CView::get("dsn", "str notNull");
$table  = CView::get("table", "str notNull");
$column = CView::get("column", "str notNull");
$start  = CView::get("start", "num default|0");
$step   = CView::get("step", "num default|500");
$total  = CView::get("total", "num");

CView::checkin();

$ds = CSQLDataSource::get($dsn);

$columns = CImportTools::getColumnsInfo($ds, $table);

$counts = $ds->loadList(
  "SELECT COUNT(*) AS row_count, `{$column}` AS value FROM `{$table}` GROUP BY `{$column}` ORDER BY row_count DESC LIMIT $start,$step;"
);
$row_count = $ds->loadResult("SELECT COUNT(*) FROM `{$table}`;");

foreach ($counts as &$_count) {
  $_count['percent'] = $_count['row_count'] / $row_count;
}

if (!$total) {
  $total = $ds->loadResult("SELECT COUNT(DISTINCT `{$column}`) FROM `{$table}`;");
}

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("table", $table);
$smarty->assign("column", $column);
$smarty->assign("columns", $columns);
$smarty->assign("counts", $counts);
$smarty->assign("total", $total);
$smarty->assign("start", $start);
$smarty->assign("step", $step);
$smarty->display("inc_vw_table_distinct_data.tpl");