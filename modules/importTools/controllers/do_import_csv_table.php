<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CProgressBar;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Import\ImportTools\CCSVImport;
use Ox\Import\ImportTools\CImportTools;

CCanDo::checkAdmin();

$dsn           = CView::post("dsn", "str");
$table         = CView::post("table", "str");
$csv_path      = CView::post("csv_path", "str", true);
$csv_extension = CView::post("csv_extension", "str");
$callback      = CView::post("callback", "str");
$callback_error= CView::post("callback_error", "str");

$csv_path = str_replace('\\\\', '\\', $csv_path);
CView::setSession("csv_path", $csv_path);

$file = "$csv_path/$table.$csv_extension";
if (!is_readable($file)) {
  if ($callback_error) {
    CAppUI::callbackAjax("window.parent.$callback_error", $table, "Fichier manquant pour la table $table");
    CApp::rip();
  }
}

$ds = CSQLDataSource::get($dsn);
$columns = CImportTools::getColumnsInfo($ds, $table);
$columns = array_keys($columns);

$csv = new CCSVImport("$csv_path/$table.$csv_extension");
$lines = $csv->csv->countLines();

$csv->csv->setColumnNames($columns);
$csv->chunk_callback_size = round($lines / 100);

$progress = new CProgressBar("csv-import-table-$table", $lines);

$csv->chunk_callback = function ($n) use ($progress) {
  $progress->advTo($n);
};

$csv->importTable($dsn, $table);

if ($callback) {
  CAppUI::callbackAjax("window.parent.$callback");
}

CApp::rip();
