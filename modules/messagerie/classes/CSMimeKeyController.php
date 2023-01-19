<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\CValue;

/**
 * Description
 */
class CSMimeKeyController extends CDoObjectAddEdit {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct('CSMimeKey', 's_mime_key_id');

    $this->redirect = 'm=messagerie';
  }

  /**
   * @inheritdoc
   */
  public function doStore() {
    $s_mime_key_id = CValue::post('s_mime_key_id');
    $certificate_type = CValue::post('_certificate_type', 'pem');
    $_passphrase = CValue::post('_passphrase', null);
    $source_id = CValue::post('source_id');
    
    $source = CMbObject::loadFromGuid("CSourcePOP-$source_id");

    if ($source->_id) {
      $cert_path = '';
      
      if (isset($_FILES['certificate'])) {
        if ($_FILES['certificate']['error'] === 0) {
          $certificate = file_get_contents($_FILES['certificate']['tmp_name']);
          @unlink($_FILES['certificate']['tmp_name']);

          /* If the certifcate is in PKCS12 format, we convert it to PEM */
          if ($certificate_type == 'pkcs12') {
            $pkcs12_passphrase = CValue::post('_pkcs12_passphrase');
            if (!$_passphrase) {
              $_passphrase = null;
            }
            $certificate       = CMbSecurity::convertPKCS12ToPEM($certificate, $pkcs12_passphrase, $_passphrase);
            if (!$certificate) {
              CAppUI::setMsg("CSMimeKey-msg-convert-pkcs12-error", UI_MSG_ERROR);
              return;
            }
            $certificate = "{$certificate['cert']}{$certificate['pkey']}";
          }

          $certificate = str_replace("\r", '', $certificate);
          $cert_path = CSMimeHandler::getCertificatePath($source);

          if (!CSMimeHandler::setCertificateFor($source, $certificate)) {
            CAppUI::setMsg("CSMimeKey-msg-upload-cert-error", UI_MSG_ERROR);
            return;
          }
        }
        else {
          CAppUI::setMsg(CAppUI::tr("CFile-msg-upload-error-" . $_FILES['certificate']["error"]), UI_MSG_ERROR);
          return;
        }
      }

      $s_mime_key = new CSMimeKey();
      if ($s_mime_key_id) {
        $s_mime_key = CMbObject::loadFromGuid("CSMimeKey-$s_mime_key_id");
      }

      $s_mime_key->source_id = $source_id;

      if ($s_mime_key->_id && $_passphrase != '') {
        $s_mime_key->_modify = true;
      }

      $s_mime_key->_passphrase = $_passphrase;
      $s_mime_key->cert_path = $cert_path;
      
      if ($msg = $s_mime_key->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        return;
      }
      
      $msg = 'CSMimeKey-msg-create';
      if ($s_mime_key_id) {
        $msg = 'CSMimeKey-msg-modify';
      }

      CAppUI::setMsg($msg, UI_MSG_OK);
    }
  }
}