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

CCanDo::checkAdmin();

$dsn    = CView::get("dsn", "str");
$table  = CView::get("table", "str");
$column = CView::get("column", "str");

CView::enforceSlave();
CView::checkin();

$ds = CSQLDataSource::get($dsn);

$columns = CImportTools::getColumnsInfo($ds, $table);

$counts = $ds->loadHash(
  "SELECT MIN(`{$column}`) AS min, MAX(`{$column}`) AS max FROM `{$table}`;"
);

$nulls = $ds->loadResult(
  "SELECT COUNT(*) as count FROM `{$table}` WHERE `{$column}` IS NULL OR `{$column}` = '';"
);

$metrics = array(
  'min'  => $counts['min'],
  'max'  => $counts['max'],
  'null' => $nulls,
);

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("table", $table);
$smarty->assign("column", $column);
$smarty->assign("columns", $columns);
$smarty->assign("metrics", $metrics);
$smarty->display("inc_vw_column_metrics.tpl");