<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Rgpd\CRGPDConsent;
use Ox\Mediboard\Files\CFile;

CCanDo::check();

$consent_id = CView::post('rgpd_consent_id', 'ref class|CRGPDConsent notNull');
$status     = CView::post('status', 'enum list|accepted|refused notNull');
$file       = CValue::files('formfile');

CView::checkin();

$consent = new CRGPDConsent();
$consent->load($consent_id);

if (!$consent || !$consent->_id || !$file || !isset($file['error']) || reset($file['error'])) {
  CAppUI::commonError();
}

$file_to_store = array();
foreach ($file as $_field => $_values) {
  if ($_field == 'name') {
    if (!array_filter($_values)) {
      break;
    }

    foreach ($_values as $_key => $_value) {
      $file_to_store[$_key] = array(
        $_field => $_value
      );
    }
  }
  else {
    foreach ($_values as $_key => $_value) {
      $file_to_store[$_key][$_field] = $_value;
    }
  }
}

$file_to_store = reset($file_to_store);

$proof_file            = new CFile();
$proof_file->file_name = $file_to_store['name'];
$proof_file->file_type = $file_to_store['type'];
$proof_file->doc_size  = $file_to_store['size'];
$proof_file->file_date = CMbDT::dateTime();
$proof_file->file_type = CMbPath::guessMimeType($proof_file->file_name);
$proof_file->author_id = CUser::get()->_id;

$proof_file->setObject($consent);
$proof_file->fillFields();

// Renaming file name without mimetype because of CFile::loadNamedFile
$proof_file->file_name = $consent->getProofFileName();

$proof_file->setMoveTempFrom($file_to_store['tmp_name']);

// Load the previous proof file
$proof = $consent->loadProofFile();

if ($msg = $proof_file->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

// Remove the previous proof file
if ($proof && $proof->_id) {
  $proof->delete();
}

// Renaming file name because we must delete previous proof file AFTER the new one's creation so its name does not fit the loadNamedFile pattern
$proof_file->file_name = $consent->getProofFileName();
$proof_file->store();

CAppUI::setMsg("{$proof_file->_class}-msg-create", UI_MSG_OK);

$consent->computeFileHash($proof_file);

switch ($status) {
  case 'accepted':
    $consent->markAsAccepted();
    break;

  case 'refused':
    $consent->markAsRefused();
    break;

  default:
    CAppUI::commonError();
}

CApp::rip();