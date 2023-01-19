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
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Mediboard\Apicrypt\CApicrypt;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CContentAny;
use Ox\Mediboard\System\CContentHTML;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;
use phpmailerException;

/**
 * Description
 */
class CUserMailController extends CDoObjectAddEdit {

  /**
   * @inheritdoc
   */
  function __construct() {
    parent::__construct('CUserMail', 'user_mail_id');

    $this->redirect = 'm=messagerie';
  }

  /**
   * @inheritdoc
   */
  function doStore() {
    /** @var CUserMail $mail */
    $mail = $this->_obj;

    $mail->date_inbox = CMbDT::dateTime();
    $mail->date_read = $mail->date_inbox;

    $content_html = new CContentHTML();

    $mail->_content = CUserMail::purifyHTML($mail->_content);
    $content_html->content = $mail->_content;

    if (!$msg = $content_html->store()) {
      $mail->text_html_id = $content_html->_id;
      $mail->_text_html = $content_html;
    }

    $content_plain = new CContentAny();
    $content_plain->content = strip_tags($mail->_content);
    if (!$msg = $content_plain->store()) {
      $mail->text_plain_id = $content_plain->_id;
    }

    $hash = CMbSecurity::hash(CMbSecurity::SHA256, "==FROM==\n$mail->from\n==TO==\n$mail->to\n==SUBJECT==\n$mail->subject\n==CONTENT==\n$mail->_content");

    if ($msg = $mail->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      return parent::doStore();
    }

    $action = CValue::post('action');

    switch ($action) {
      case 'draft':
        $mail->draft = '1';

        if ($mail->folder_id) {
          $folder = $mail->loadFolder();
          if ($folder->type != 'drafts') {
            $mail->folder_id = '';
          }
        }

        CAppUI::setMsg('CUserMail-msg-drafted', UI_MSG_OK);
        break;

      case 'send':
        $account = $mail->loadAccount();

        if ($mail->is_apicrypt) {
          /** @var CSourceSMTP $smtp */
          $smtp = CExchangeSource::get("mediuser-$account->object_id-apicrypt", 'smtp');
        }
        else {
          /** @var CSourceSMTP $smtp */
          $smtp = CExchangeSource::get("mediuser-$account->object_id", 'smtp');
          $user = CMediusers::get($account->object_id);
          $smtp->setSenderNameFromUser($user);
        }

        if ($smtp->asynchronous == '1') {
          $mail->to_send = '1';
          $mail->draft = '1';

          CAppUI::setMsg('CUserMail-to_send', UI_MSG_OK);
        }
        else {
          $smtp->init();

          foreach (explode(',', $mail->to) as $recipient) {
            list($address, $name) = self::parseRecipientString($recipient);

            if (!CMbString::isEmailValid($address)) {
              return CAppUI::setMsg('CUserMail-error-address_invalid-to', UI_MSG_ERROR, $address);
            }
            $smtp->addTo($address, $name);
          }

          if ($mail->cc != '') {
            foreach (explode(',', $mail->cc) as $recipient) {
              list($address, $name) = self::parseRecipientString($recipient);

              if (!CMbString::isEmailValid($address)) {
                return CAppUI::setMsg('CUserMail-error-address_invalid-cc', UI_MSG_ERROR, $address);
              }
              $smtp->addCc($address, $name);
            }
          }

          if ($mail->bcc != '') {
            foreach (explode(',', $mail->bcc) as $recipient) {
              list($address, $name) = self::parseRecipientString($recipient);

              if (!CMbString::isEmailValid($address)) {
                return CAppUI::setMsg('CUserMail-error-address_invalid-bcc', UI_MSG_ERROR, $address);
              }
              $smtp->addBcc($address, $name);
            }
          }

          $smtp->setSubject($mail->subject);

          if ($mail->is_apicrypt) {
            $receiver = explode(',', $mail->to);
            $body = CApicrypt::encryptBody($account->object_id, $receiver[0], $mail->_content);
            $smtp->setBody($body);
          }
          else {
            $smtp->setBody($mail->_content);
          }

          /** @var CMailAttachments[] $attachments */
          $attachments = $mail->loadAttachments();
          foreach ($attachments as $_attachment) {
            $file = $_attachment->loadFiles();
            $smtp->addAttachment($file->_file_path, $file->file_name);
          }

          try {
            $smtp->send();
            $mail->sent = '1';
            $mail->draft = '0';

            if ($mail->folder_id) {
              $folder = $mail->loadFolder();
              if ($folder->type != 'sent') {
                $mail->folder_id = '';
              }
            }

            CAppUI::setMsg('CUserMail-msg-sent', UI_MSG_OK);
          }
          catch (phpmailerException $e) {
            CAppUI::setMsg($e->errorMessage(), UI_MSG_ERROR);
          }
          catch (CMbException $e) {
            $e->stepAjax();
          }
        }
        break;
      default:
    }

    $mail->store();

    if (CAppUI::isMsgOK() && $this->redirectStore) {
      $this->redirect =& $this->redirectStore;
    }

    if (!CAppUI::isMsgOK() && $this->redirectError) {
      $this->redirect =& $this->redirectError;
    }
  }

  /**
   * Extracts the address and the optional name from the recipient's string
   *
   * @param string $recipient
   *
   * @return array
   */
  protected static function parseRecipientString($recipient) {
    $name = '';
    if (($begin = strpos($recipient, "<")) !== false && ($end = strpos($recipient, ">")) !== false) {
      $name = substr($recipient, 0, ($begin - 1));
      $address = substr($recipient, ($begin + 1), ($end - $begin - 1));
    }
    else {
      $address = $recipient;
    }

    return [$address, $name];
  }
}
