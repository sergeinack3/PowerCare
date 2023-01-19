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
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
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

$file_checker = new CFileChecker($files_to_check);
$infos = $file_checker->check();

foreach ($infos as $_object_class => $_type) {
  CApp::log("{$_object_class} OK", $_type['ok']);
  CApp::log("{$_object_class} EMPTY_OK", $_type['empty_ok']);
  CApp::log("{$_object_class} NOK", $_type['nok']);
  CApp::log("{$_object_class} EMPTY_NOK", $_type['empty_nok']);
}