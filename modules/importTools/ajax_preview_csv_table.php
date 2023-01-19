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
use Ox\Core\FileUtil\CCSVFile;

CCanDo::checkAdmin();

$dsn   = CView::post("dsn", "str");
$table = CView::post("table", "str");
$csv_path = CView::post("csv_path", "str", true);

CView::checkin();

$csv = new CCSVFile($csv_path, CCSVFile::PROFILE_AUTO);
$rows = array();

$n = 10;
while ($n-- > 0) {
  $rows[] = $csv->readLine();
}

$smarty = new CSmartyDP();
$smarty->assign("dsn", $dsn);
$smarty->assign("table", $table);
$smarty->assign("rows", $rows);
$smarty->display("inc_preview_csv_table.tpl");
