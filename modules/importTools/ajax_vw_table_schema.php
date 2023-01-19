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

$dsn   = CView::get('dsn', 'str notNull');
$table = CView::get('table', 'str notNull');

CView::checkin();

$ds         = CSQLDataSource::get($dsn);
$table_info = CImportTools::getTableInfo($ds, $table);

$smarty = new CSmartyDP();
$smarty->assign('dsn', $dsn);
$smarty->assign('table', $table);
$smarty->assign('table_info', $table_info);
$smarty->display('vw_table_schema.tpl');
