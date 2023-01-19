<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;

/**
 * Description
 */
class CMailAttachmentController extends CDoObjectAddEdit {
  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct('CMailAttachments', 'user_mail_attachment_id');

    $this->redirect = 'm=messagerie';
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    if (isset($_FILES['attachment'])) {
      $mail_id = CValue::post('mail_id');

      $mail = new CUserMail();
      $mail->load($mail_id);

      $files = array();

      foreach ($_FILES['attachment']['error'] as $key => $file_error) {
        if (isset($_FILES['attachment']['name'][$key])) {
          $files[] = array(
            'name'     => $_FILES['attachment']['name'][$key],
            'tmp_name' => $_FILES['attachment']['tmp_name'][$key],
            'error'    => $_FILES['attachment']['error'][$key],
            'size'     => $_FILES['attachment']['size'][$key],
          );
        }
      }

      foreach ($files as $_key => $_file) {
        if ($_file['error'] == UPLOAD_ERR_NO_FILE) {
          continue;
        }

        if ($_file['error'] != 0) {
          CAppUI::setMsg(CAppUI::tr("CFile-msg-upload-error-" . $_file["error"]), UI_MSG_ERROR);
          continue;
        }

        $attachment              = new CMailAttachments();
        $attachment->name        = $_file['name'];
        $content_type            = mime_content_type($_file['tmp_name']);
        $attachment->type        = $attachment->getTypeInt($content_type);
        $attachment->bytes       = $_file['size'];
        $attachment->mail_id     = $mail_id;
        $content_type            = explode('/', $content_type);
        $attachment->subtype     = strtoupper($content_type[1]);
        $attachment->disposition = 'ATTACHMENT';
        $attachment->extension   = substr(strrchr($attachment->name, '.'), 1);
        $attachment->part        = $mail->countBackRefs('mail_attachments') + 1;

        $attachment->store();

        $file = new CFile();

        $file->setObject($attachment);
        $file->author_id = CAppUI::$user->_id;
        $file->file_name = $attachment->name;
        $file->file_date = CMbDT::dateTime();
        $file->fillFields();
        $file->updateFormFields();
        $file->doc_size = $attachment->bytes;
        $file->file_type = mime_content_type($_file['tmp_name']);

        $file->setMoveTempFrom($_file['tmp_name']);

        if ($msg = $file->store()) {
          CAppUI::setMsg(CAppUI::tr('CMailAttachments-error-upload-file') . ':' . CAppUI::tr($msg), UI_MSG_ERROR);
          CApp::rip();
        }

        $attachment->file_id = $file->_id;
        if ($msg = $attachment->store()) {
          CAppUI::setMsg($msg, UI_MSG_ERROR);
          CApp::rip();
        }
      }

      CAppUI::setMsg('CMailAttachments-msg-added', UI_MSG_OK);
    }
    else {
      parent::doStore();
    }
  }
}
