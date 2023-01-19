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

$dsn    = CView::get('dsn', 'str notNull');
$table  = CView::get('table', 'str notNull');
$where  = CView::get('where', 'str');
$select = CView::get('select', 'str');
$start  = CView::get('start', 'num default|0');
$step   = CView::get('step', 'num default|100');

CView::checkin();

CView::enforceSlave();

$ds = CSQLDataSource::get($dsn);

$table_info = CImportTools::getTableInfo($ds, $table);

$request = CImportTools::prepareQuery($ds, $table, $select, $table_info['columns'], $where);

$request_count = clone $request;

$request->setLimit("$start,$step");

$rows = $ds->loadList($request->makeSelect());
$total = $ds->loadResult($request_count->makeSelectCount());

$order_column = null;
$order_way = null;
$tooltip = null;

$smarty = new CSmartyDP();
$smarty->assign('dsn', $dsn);
$smarty->assign('table', $table);
$smarty->assign('table_info', $table_info);
$smarty->assign('columns', $table_info['columns']);
$smarty->assign('rows', $rows);
$smarty->assign('order_column', $order_column);
$smarty->assign('order_way', $order_way);
$smarty->assign('tooltip', $tooltip);
$smarty->assign('start', $start);
$smarty->assign('count', $step);
$smarty->assign('total', $total);
$smarty->display('inc_vw_pop_table_lines.tpl');
