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
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$filename   = CValue::post('filename');
$patient_id = CValue::post('patient_id');

if (!$patient_id) {
  CAppUI::stepAjax('common-error-You have to select a patient', UI_MSG_ERROR);

  return;
}

$dir = rtrim(CAppUI::conf('dPfiles import_dir'), '/') . '/';

if (!$dir) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('config-dPfiles-import_dir-desc'));
}

if (!$user_id = CAppUI::conf('dPfiles import_mediuser_id')) {
  CAppUI::stepAjax('common-error-Missing parameter: %s', UI_MSG_ERROR, CAppUI::tr('User'));
}

if (!is_readable("{$dir}{$filename}")) {
  CAppUI::stepAjax("common-error-Unable to read the file", UI_MSG_ERROR);
}

$patient = new CPatient();
$patient->load($patient_id);

if ($patient && $patient->_id) {
  $file            = new CFile();
  $file->file_name = $filename;
  $file->author_id = $user_id;
  $file->file_type = CMbPath::guessMimeType($filename);
  $file->doc_size  = filesize("{$dir}{$filename}");

  $file->setObject($patient);
  $file->fillFields();

  $file->setCopyFrom("{$dir}{$filename}");

  if ($msg = $file->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    CAppUI::stepAjax("Fichier associé au patient {$patient}", UI_MSG_OK);
  }
}

CApp::rip();