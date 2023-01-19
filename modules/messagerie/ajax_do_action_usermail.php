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
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Messagerie\CUserMailFolder;

/* The deletion of the mails on the mailbox via POP or IMAP cna take some time */
CApp::setTimeLimit(60);

CCanDo::checkRead();

$usermail_ids = json_decode(stripslashes(CView::get('usermail_ids', 'str default|[]')));
$action = CView::get('action', 'enum list|unarchive|archive|unfavour|favour|delete|mark_read|mark_unread|reset_retries|move');
$folder_id = CView::get('folder_id', 'ref class|CUserMailFolder');

CView::checkin();

if (empty($usermail_ids)) {
  CAppUI::stepAjax('CUserMail-msg-no_mail_selected', UI_MSG_WARNING);
  CApp::rip();
}

/* Connect to the pop server in case of deletion */
if ($action == 'delete') {
  $pop = null;
}

foreach ($usermail_ids as $usermail_id) {
  $mail = new CUserMail();
  $mail->load($usermail_id);

  switch ($action) {
    case 'unarchive':
      $mail->archived = 0;

      if (!$mail->date_read) {
        $mail->date_read = CMbDT::dateTime();
      }

      if ($mail->folder_id) {
        $folder = $mail->loadFolder();
        if ($folder->type != 'inbox') {
          $mail->folder_id = '';
        }
      }
      break;
    case 'archive':
      $mail->archived = 1;

      if (!$mail->date_read) {
        $mail->date_read = CMbDT::dateTime();
      }

      if ($mail->folder_id) {
        $folder = $mail->loadFolder();
        if ($folder->type != 'archived') {
          $mail->folder_id = '';
        }
      }
      break;

    case 'unfavour':
      $mail->favorite = 0;

      if ($mail->folder_id) {
        $folder = $mail->loadFolder();
        if ($folder->type != 'inbox') {
          $mail->folder_id = '';
        }
      }
      break;
    case 'favour':
      $mail->favorite = 1;

      if ($mail->folder_id) {
        $folder = $mail->loadFolder();
        if ($folder->type != 'favorites') {
          $mail->folder_id = '';
        }
      }
      break;

    case 'delete':
      $uid = $mail->uid;
      if ($msg = $mail->delete()) {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
      }
      /* Deleting the mail in the mailbox */
      elseif ($uid) {
        /* Open the pop connection */
        if (!$pop) {
          $pop = new CPop($mail->loadAccount());
          $pop->open();
        }

        if ($pop instanceof CPop && $pop->_is_open) {
          $pop->deleteMail($uid);
        }
      }
      break;

    case 'mark_read':
      $mail->date_read = CMbDT::dateTime();
      break;

    case 'mark_unread':
      $mail->date_read = '';
      break;

    case 'reset_retries':
      $mail->retry_count = 0;
      break;
    case 'move':
      $mail->folder_id = $folder_id;

      $folder = CUserMailFolder::loadFromGuid("CUserMailFolder-$folder_id");
      switch ($folder->type) {
        case 'favorites':
          $mail->favorite = 1;
          break;
        case 'archived':
          $mail->archived = 1;
          break;
        default:
          $mail->favorite = 0;
          $mail->archived = 0;
      }

    default:
  }

  if ($action != 'delete') {
    if ($msg = $mail->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
  }
}

/* Connect to the pop server in case of deletion */
if ($action == 'delete' && $pop instanceof CPop && $pop->_is_open) {
  $pop->expunge();
  $pop->close();
}

$msg = "CUserMail-msg-$action";
if (count($usermail_ids) > 1) {
  $msg .= '-pl';
}

CAppUI::stepAjax($msg, UI_MSG_OK);