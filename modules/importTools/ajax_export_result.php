<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;


CCanDo::checkAdmin();

$dsn = CView::get('dsn', 'str notNull');
$table = CView::get('table', 'str notNull');
$where = CView::get('where', 'str');
$select = CView::get('select', 'str');
$step = CView::get('step', 'num min|1 default|50000');

CView::checkin();

$step = ($step) ?: 50000;

CView::enforceSlave();

$ds = CSQLDataSource::get($dsn);

$table_info = CImportTools::getTableInfo($ds, $table);
$columns = $table_info['columns'];

$request = CImportTools::prepareQuery($ds, $table, $select, $columns, $where);

$request_count = clone $request;
$total = $ds->loadResult($request_count->makeSelectCount());

$csv = null;
for ($i = 0; $i < $total; $i+= $step) {
  $request->setLimit("$i,$step");
  $write_head = ($i == 0);
  $csv = $ds->fetchCSVFile($request->makeSelect(), $csv, $write_head);
}

$csv->stream('query-result', true);