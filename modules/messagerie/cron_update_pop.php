<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Interop\Ftp\CFTP;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Messagerie\CMimeParser;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CSMimeHandler;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\CSourcePOP;

/**
 * Update the source pop account
 */
set_time_limit(600);

CCanDo::checkRead();
CPop::checkImapLib();

$nbAccount = CAppUI::conf("messagerie CronJob_nbMail");
$older = CAppUI::conf("messagerie CronJob_olderThan");

$limitMail = CView::get("limit", 'num default|' . (CAppUI::gconf("messagerie messagerie_externe limit_external_mail")+1));
$retrieve_files = boolval(CAppUI::gconf('messagerie messagerie_externe retrieve_files_on_update'));

$account_id = CView::get("account_id", 'num');
$import = CView::get("import", 'bool default|0');

CView::checkin();

//source
$source = new CSourcePOP();
$where = array();
$where["active"] = "= '1'";

if ($account_id) {
  $where["source_pop_id"] = " = '$account_id'";
}
else {
  $where["cron_update"] = "= '1'";
}

$order = "last_update ASC";
$limit = "0, $nbAccount";
$sources = $source->loadList($where, $order, $limit);

/** @var CSourcePOP[] $sources */
foreach ($sources as $_source) {

  $user = $_source->loadRefMetaObject();

  //no user => next
  if (!$_source->user) {
    CAppUI::stepAjax("pas d'utilisateur pour cette source %s", UI_MSG_WARNING, $_source->_view);
    continue;
  }

  // when a mail is copied in mediboard, will it be marked as read on the server ?
  $markReadServer = 0;
  $prefs = CPreferences::get($_source->object_id);   //for user_id
  $markReadServer = (isset($prefs["markMailOnServerAsRead"])) ? $prefs["markMailOnServerAsRead"] : CAppUI::pref("markMailOnServerAsRead");
  $archivedOnReception = (isset($prefs["mailReadOnServerGoToArchived"])) ? $prefs["mailReadOnServerGoToArchived"] : CAppUI::pref("mailReadOnServerGoToArchived");

  //last email uid from mediboard
  $mbMailUid = (CUserMail::getLastMailUid($_source->_id)) ? CUserMail::getLastMailUid($_source->_id) : 0;

  // last email datetime
  if ($import) {
    $firstEmailDate = CUserMail::getFirstMailDate($_source->_id);
    $firstCheck = $firstEmailDate;
    $firstCheck = CMbDT::dateTime("+1 DAY", $firstCheck);
    $month_number = CMbDT::format($firstCheck, "%m");
    $months = array_keys(CFTP::$month_to_number, $month_number);
    $month = reset($months);
    $dateIMAP = CMbDT::format($firstCheck, "%d-$month-%Y");
  }
  elseif (!CUserMail::hasReceivedMails($_source->_id)) {
    $import = true;
    $firstCheck = CMbDT::dateTime("+1 DAY");
    $month_number = CMbDT::format($firstCheck, "%m");
    $months = array_keys(CFTP::$month_to_number, $month_number);
    $month = reset($months);
    $dateIMAP = CMbDT::format($firstCheck, "%d-$month-%Y");
  }
  else {
    $lastEmailDate = CUserMail::getLastMailDate($_source->_id);
    $firstCheck = $lastEmailDate;
    $firstCheck = CMbDT::dateTime("-1 DAY", $firstCheck);
    $month_number = CMbDT::format($firstCheck, "%m");
    $months = array_keys(CFTP::$month_to_number, $month_number);
    $month = reset($months);
    $dateIMAP = CMbDT::format($firstCheck, "%d-$month-%Y");
  }


  //pop open account
  $pop = new CPop($_source);
  if (!$pop->open()) {
    CAppUI::stepAjax("Impossible de se connecter à la source (open) %s", UI_MSG_WARNING, $_source->_view);
    continue;
  }

  //If import mode (get before actual)
  if ($import) {
    $unseen = $pop->search('BEFORE "'.$dateIMAP.'"', true);
  }
  else {
    $unseen = $pop->search('SINCE "'.$dateIMAP.'"', true);
  }

  if (is_array($unseen)) {
    array_splice($unseen, $limitMail);
  }
  else {
    $unseen = [];
  }

  $results = count($unseen);
  $total = imap_num_msg($pop->_mailbox);

  //if get last email => check if uid server is > maxuidMb
  // @TODO : temporarly removed, we already get the more recent mail for filter
  /*if (!$import) {
    foreach ($unseen as $key => $_unseen) {
      if ($_unseen < $mbMailUid) {
        unset($unseen[$key]);
      }
    }
  }*/

  if (count($unseen)>0) {
    $unread = 0;    //unseen mail
    $loop = 0;      //loop of foreach
    $created = 0;
    foreach ($unseen as $_mail) {
      $pop->cleanTemp();

      $mail_unseen = new CUserMail();
      $mail_unseen->account_id = $_source->_id;
      $mail_unseen->account_class = $_source->_class;

      $struct = imap_fetchstructure($pop->_mailbox, $_mail, FT_UID);
      $headers = imap_fetchheader($pop->_mailbox, $_mail, FT_UID);

      $header = $pop->header($_mail);
      if ($pop->checkSMIME($_mail)) {
        $content = array('text' => array('plain' => $pop->body($_mail)));
      }
      else {
        $content = $pop->getFullBody($_mail, false, false, true);
      }

      $hash = $mail_unseen->makeHash($header, $content);

      //mail non existant
      if (!$mail_unseen->loadMatchingFromHash($hash)) {
        $mail_unseen->setHeaderFromSource($header);//sent ?

        if (strpos($mail_unseen->from, $_source->user) !== false) {
          $mail_unseen->sent = 1;
        }

        //unread increment
        if (!$mail_unseen->date_read) {
          $unread++;
        }

        //read on server + pref + not sent => archived !
        if ($mail_unseen->date_read && $archivedOnReception && !$mail_unseen->sent) {
          $mail_unseen->archived = 1;
        }

        /* If the mail is encrypted (S/Mime) */
        if ($pop->checkSMIME($_mail)) {
          $encrypted = $pop->getEncryptedMail($_mail);
          $decrypted = CSMimeHandler::decryptSMime($encrypted, $_source);
          $parser = new CMimeParser($mail_unseen, $decrypted);
          $mail_unseen = $parser->parse();

          /* If the PEAR extension Mail/mimeDecode is not included */
          if (!$mail_unseen) {
            continue;
          }

          //text plain
          $mail_unseen->getPlainText($_source->object_id);
          //text html
          $mail_unseen->getHtmlText($_source->object_id);

          $mail_unseen->is_hprimnet = '1';

          //store the usermail
          if (!$msg = $mail_unseen->store()) {
            $created++;
          }

          foreach ($mail_unseen->_attachments as $_attachment) {
            $_attachment->mail_id = $mail_unseen->_id;
            $msg = $_attachment->store();

            $_file = new CFile();
            $_file->setObject($_attachment);
            $_file->author_id  = CAppUI::$user->_id;
            $_file->file_name = $_attachment->name;
            $_file->file_type  = $_attachment->getType($_attachment->type, $_attachment->subtype);
            $_file->fillFields();
            $_file->updateFormFields();
            $_file->setContent($_attachment->_content);
            $msg = $_file->store();

            $_attachment->file_id = $_file->_id;
            $msg = $_attachment->store();
          }
        }
        else {
          $mail_unseen->setContentFromSource($pop->getFullBody($_mail, false, false, true));

          //text plain
          $mail_unseen->getPlainText($_source->object_id);
          //text html
          $mail_unseen->getHtmlText($_source->object_id);

          //store the usermail
          if (!$msg = $mail_unseen->store()) {
            $created++;
          }

          //attachments list
          $pop->cleanTemp();
          $attachs = $pop->getListAttachments($_mail);
          $mail_unseen->attachFiles($attachs, $pop, $retrieve_files);
        }
      }
      // mail existe
      else {
        /* Mise à jour de l'UID en cas de changement des UIDs sur le serveur */
        if ($mail_unseen->uid != $header->uid) {
          $mail_unseen->uid = $header->uid;
          $mail_unseen->store();
        }
        // si le mail est lu sur MB mais non lu sur IMAP => on le flag
        if ($mail_unseen->date_read) {
          $pop->setFlag($_mail, "\\Seen");
        }
      }
      $loop++;
    } //foreach

    //set email as read in imap/pop server
    if ($markReadServer) {
      $pop->setFlag(implode(",", $unseen), "\\Seen");
    }

    //number of mails gathered
    CAppUI::stepAjax("CPop-msg-newMsgs", UI_MSG_OK, $unread, $created, $results, $total);
  }
  else {
    CAppUI::stepAjax("CPop-msg-nonewMsg", UI_MSG_OK, $_source->libelle);
  }

  $_source->last_update = CMbDT::dateTime();

  $_source->store();
  $pop->close();
}
