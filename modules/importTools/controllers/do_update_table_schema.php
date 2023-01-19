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
use Ox\Core\CMbArray;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Import\ImportTools\CImportTools;

CCanDo::checkAdmin();

$dsn         = CView::post('dsn', 'str notNull');
$table       = CView::post('table', 'str notNull');
$column_name = CView::post('column_name', 'str notNull');
$column_type = CView::post('column_type', 'str notNull');

CView::checkin();

if (CImportTools::checkDSN($dsn)) {
  CAppUI::stepAjax('mod-importTools-no-std', UI_MSG_ERROR);
}

if (!$dsn || !$table || !$column_name || !$column_type) {
  CAppUI::commonError();
}

if (!in_array($column_type, CMbArray::array_flatten(CImportTools::$authorized_datatypes))) {
  CAppUI::commonError();
}

$ds = CSQLDataSource::get($dsn);

$table_info = CImportTools::getTableInfo($ds, $table);

if (!$table_info || !$table_info['columns'] || !in_array($column_name, array_keys($table_info['columns']))) {
  CAppUI::commonError();
}

$query = "ALTER TABLE `{$table}`
          MODIFY `{$column_name}` {$column_type}";

if ($ds->exec($query)) {
  CAppUI::stepAjax('Schéma modifié', UI_MSG_OK);
}
else {
  CAppUI::commonError();
}

CApp::rip();
