<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Apicrypt\CApicrypt;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

set_time_limit(300);

CCanDo::checkAdmin();

CView::checkin();

$mail = new CUserMail();
$where = array(
  'to_send' => " = '1'",
  'retry_count' => " < " . CAppUI::gconf('messagerie messagerie_externe retry_number')
);
$mail->to_send = '1';
/** @var CUserMail[] $mails */
$mails = $mail->loadList($where, null, '0, 100');
$errors = array();

foreach ($mails as $_mail) {
  /** @var CSourceSMTP $_source */
  $_account = $_mail->loadAccount();
  if ($_account->_class == 'CSourcePOP') {
    $_user_id = $_account->object_id;
    if ($mail->is_apicrypt) {
      /** @var CSourceSMTP $smtp */
      $_source = CExchangeSource::get("mediuser-$_account->object_id-apicrypt", 'smtp');
    }
    else {
      /** @var CSourceSMTP $smtp */
      $_source = CExchangeSource::get("mediuser-$_account->object_id", 'smtp');
    }
  }
  else {
    $_source = $_account;
    $_user_id = explode('-', $_source->name);
    $_user_id = $_user_id[1];
  }

  $_mail->loadAttachments();
  if ($_mail->text_html_id) {
    $_content = $_mail->loadContentHTML();
  }
  else {
    $_content = $_mail->loadContentPlain();
  }

  $_source->init();
  $_source->setRecipient($_mail->to);
  $_source->setSubject($_mail->subject);

  if ($_mail->is_apicrypt) {
    $receiver = explode(',', $mail->to);
    $body = CApicrypt::encryptBody($_user_id, $receiver[0], $_content->content);
    $_source->setBody($body);
  }
  else {
    $_source->setBody($_content->content);
  }

  foreach ($_mail->_attachments as $_attachment) {
    $_file = $_attachment->loadFiles();
    $_source->addAttachment($_file->_file_path, $_file->file_name);
  }

  try {
    $_source->send();
    $_mail->draft = '0';
    $_mail->to_send = '0';
    $_mail->sent = '1';

    if ($mail->folder_id) {
      $folder = $mail->loadFolder();
      if ($folder->type != 'sent') {
        $mail->folder_id = '';
      }
    }

    $_mail->retry_count = 0;
  }
  catch (phpmailerException $e) {
    CApp::log("{$_source->_guid} {$_mail->_guid}:  " . $e->getMessage(), null, LoggerLevels::LEVEL_WARNING);

    $_mail->retry_count++;
  }
  catch (CMbException $e) {
    CApp::log("{$_source->_guid} {$_mail->_guid}:  " . $e->getMessage(), null, LoggerLevels::LEVEL_WARNING);

    $_mail->retry_count++;
  }

  $_mail->store();
}
