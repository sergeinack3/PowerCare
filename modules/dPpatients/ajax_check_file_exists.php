<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;

CCanDo::checkAdmin();

$file_path = CView::get('file_path', 'str');

CView::checkin();

$file_path = str_replace('\\\\', '\\', $file_path);

if (!$file_path) {
  CAppUI::stepAjax('CPatient-export-file-no-path', UI_MSG_ERROR);
}

if (file_exists($file_path) && is_file($file_path)) {
  if (!is_writable($file_path)) {
    CAppUI::stepAjax('Cpatient-export-file-permission-denied', UI_MSG_ERROR);
  }

  CAppUI::stepAjax('Cpatient-export-file-ok', UI_MSG_OK);
  CApp::rip();
}

$infos = pathinfo($file_path);

if (!is_writable($infos['dirname'])) {
  CAppUI::stepAjax('CPatient-export-dir-not-writable', UI_MSG_ERROR, $infos['dirname']);
}

CAppUI::stepAjax('CPatient-export-file-ok', UI_MSG_OK);


