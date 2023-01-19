<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCando;
use Ox\Core\CMbPath;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;

CCanDo::checkAdmin();

$tmp_filename = $_FILES['import']['tmp_name'];

CView::checkin();

if (!$tmp_filename) {
  CAppUI::js('window.parent.KerberosLDAP.uploadError()');
  CApp::rip();
}

$file = new CCSVFile($tmp_filename, CCSVFile::PROFILE_AUTO);
$file->setColumnNames(['mediboard_identifier', 'domain_identifier']);

$temp     = CAppUI::getTmpPath('kerberos_import');
$uid      = preg_replace('/[^\d]/', '', uniqid('', true));
$filename = "{$temp}/{$uid}";

CMbPath::forceDir($temp);

move_uploaded_file($tmp_filename, $filename);

// Cleanup old files (more than 4 hours old)
$other_files = glob("{$temp}/*");
$now         = time();

foreach ($other_files as $_other_file) {
  if (filemtime($_other_file) < $now - 3600 * 4) {
    unlink($_other_file);
  }
}

CAppUI::js("window.parent.KerberosLDAP.uploadSaveUID('{$uid}')");
CApp::rip();