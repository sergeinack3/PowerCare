<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;

CCanDo::checkAdmin();

$dsn    = CView::get("dsn", "str");
$table  = CView::get("table", "str");
$column = CView::get("column", "str");

CView::enforceSlave();
CView::checkin();

$db_info = CImportTools::getDatabaseStructure($dsn);

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("table", $table);
$smarty->assign("column", $column);
$smarty->assign("db_info", $db_info);
$smarty->display("inc_select_primary_key.tpl");