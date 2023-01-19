<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;

CCanDo::checkAdmin();

$object_class      = CValue::get('object_class');
$error_type        = CValue::get('error_type');

CView::enforceSlave();

$ds = CSQLDataSource::get('std');

$csv = new CCSVFile();

$fields = array(
  'object_class' => null,
  'object_id'    => null,
  'file_hash'    => null,
  'file_name'    => null,
  'db_file_date' => null,
  'fs_file_date' => null,
  'db_file_size' => null,
  'fs_file_size' => null,
  'file_path'    => null,
);

$csv->writeLine(array_keys($fields));

$db_name = CAppUI::conf("db std dbname");
$filename = "{$db_name}_file_report";
$where = array();
if ($object_class) {
  $where[] = "`file_report`.`object_class` = '$object_class'";
  $filename .= "_$object_class";
}

if ($error_type) {
  $where[] = "`file_report`.`$error_type`= '1'";
  $filename .= "_$error_type";
}

$ljoin = array();
$ljoin['files_mediboard'] = 'files_mediboard.file_real_filename = file_report.file_hash';

$request = new CRequest();
$request->addSelect('*');
$request->addTable('file_report');
$request->addLJoin($ljoin);
$request->addWhere($where);
$error_file_list = $ds->exec($request->makeSelect());

while ($_error_file = $ds->fetchAssoc($error_file_list)) {
  $_fields = array(
    'object_class' => $_error_file['object_class'],
    'object_id'    => $_error_file['object_id'],
    'file_hash'    => $_error_file['file_hash'],
    'file_name'    => $_error_file['file_name'],
    'db_file_date' => $_error_file['file_date'],
    'db_file_size' => $_error_file['doc_size'],
    'fs_file_size' => $_error_file['file_size'],
    'file_path'    => $_error_file['file_path'],
  );

  $csv->writeLine($_fields);
}

$csv->stream($filename, true);
CApp::rip();