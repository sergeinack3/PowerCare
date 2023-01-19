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
use Ox\Core\CMbPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFileChecker;

CCanDo::checkAdmin();

$file       = CValue::files('formfile');

CView::checkin();

set_time_limit(300);

$filename = '';
if ($file && $file['tmp_name']) {
  $dir      = rtrim(CAppUI::conf('root_dir'), '/\\') . '/tmp';
  $filename = "$dir/{$file['name'][0]}";

  move_uploaded_file($file['tmp_name'][0], $filename);
}
else {
  CAppUI::stepAjax('CFile-not-exists', UI_MSG_ERROR, $file_path);
}

$dest_dir = rtrim(CAppUI::conf('root_dir'), '\\/') . '/tmp/files_sizes';
CMbPath::forceDir($dest_dir);
CMbPath::emptyDir($dest_dir);
$extract = CMbPath::extract($filename, $dest_dir);

$files_to_check = array();

if ($extract) {
  $file_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dest_dir));

  foreach ($file_iterator as $_name => $_directory) {
    if (is_dir($_name) || strpos($_name, '.csv') === false) {
      continue;
    }

    CFileChecker::parseFile($_name, $files_to_check);
  }
}
else {
  CFileChecker::parseFile($filename, $files_to_check);
}

$ds = CSQLDataSource::get('std');

$where = array(
  'object_class'       => '= "CCompteRendu"',
  'file_real_filename' => $ds->prepareIn(array_keys($files_to_check)),
);

$file  = new CFile();
$files = $file->loadList($where);

$nb_files_corrected = 0;
$files_ok           = 0;

/** @var CFile $_file */
foreach ($files as $_file) {
  if ($_file->doc_size == 0) {
    $files_ok++;
    continue;
  }

  $_file->doc_size = 0;
  if ($msg = $_file->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    $nb_files_corrected++;
  }
}

CAppUI::setMsg('CFile-size ok %d', UI_MSG_OK, $files_ok);
CAppUI::setMsg('CFile-nb corrected %d', UI_MSG_OK, $nb_files_corrected);

echo CAppUI::getMsg();