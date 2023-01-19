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

$dsn   = CView::get("dsn", "str", true);
$csv_path = CView::get("csv_path", "str", true);
$csv_extension = CView::get("csv_extension", "str default|csv", true);

$info = CImportTools::getDatabaseStructure($dsn);

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("info", $info);
$smarty->assign("tables", array_keys($info["tables"]));
$smarty->assign("csv_path", $csv_path);
$smarty->assign("csv_extension", $csv_extension);
$smarty->display("vw_import_csv_tables.tpl");
