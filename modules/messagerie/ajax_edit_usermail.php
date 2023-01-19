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
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Messagerie\CMailAttachments;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkEdit();

$account_id         = CView::get('account_id', 'ref class|CSourcePOP');
$mail_id            = CView::get('mail_id', 'ref class|CUserMail');
$reply_to_id        = CView::get('reply_to_id', 'ref class|CUserMail');
$answer_to_all      = CView::get('answer_to_all', 'bool');
$forward_mail       = CView::get('forward_mail', 'bool default|0');
$contact_support_ox = CView::get('contact_support_ox', 'bool default|0');
$context            = CView::get('context', 'str');
$mail_subject       = CView::get('mail_subject', 'str');

CView::checkin();

$account = new CSourcePOP();
$account->load($account_id);

if (strpos($account->name, 'apicrypt') !== false) {
    $smtp = CExchangeSource::get("mediuser-{$account->object_id}-apicrypt", 'smtp');
} else {
    $smtp = CExchangeSource::get("mediuser-{$account->object_id}", 'smtp');
}

if (!$smtp->_id) {
    $smarty = new CSmartyDP();
    $smarty->assign('msg', CAppUI::tr('CUserMail-msg-no_smtp_source_linked_to_pop_account'));
    $smarty->assign('type', 'error');
    $smarty->assign('modal', 1);
    $smarty->assign('close_modal', 1);
    $smarty->display('inc_display_msg.tpl');
    CApp::rip();
}

$mail = new CUserMail();
if ($mail_id && !$forward_mail) {
    $mail->load($mail_id);
    if ($mail->text_html_id) {
        $mail->loadContentHTML();
        $mail->_content = $mail->_text_html->content;
    } elseif ($mail->text_plain_id) {
        $mail->loadContentPlain();
        $mail->_content = $mail->_text_plain->content;
    }
} else {
    $mail->from          = $account->user;
    $mail->account_class = $account->_class;
    $mail->account_id    = $account->_id;
    $mail->draft         = '1';

    if ($reply_to_id) {
        $mail->in_reply_to_id = $reply_to_id;
        $reply_to             = new CUserMail();
        $reply_to->load($reply_to_id);
        $mail->to = $reply_to->from;
        strpos(
            $reply_to->subject,
            'Re:'
        ) === false ? $mail->subject = "Re: $reply_to->subject" : $mail->subject = $reply_to->subject;

        if ($answer_to_all) {
            $mail->cc = $reply_to->cc;

            /* Récupération des destinataires différents de l'adresse de compte smtp */
            $receivers = explode(',', $reply_to->to);
            foreach ($receivers as $receiver) {
                if ($receiver != '' && strpos($receiver, $smtp->email) === false) {
                    $mail->to .= ',' . $receiver;
                }
            }
        }
    }

    if ($forward_mail) {
        $forward = new CUserMail();
        $forward->load($mail_id);

        strpos(
            $forward->subject,
            'Fwd:'
        ) === false ? $mail->subject = "Fwd: $forward->subject" : $mail->subject = $forward->subject;

        $mail->_content = "<br /><br /><div>---------- ".CAppUI::tr('CUserMail-msg-Forwarded message')." ---------<br />
" . CAppUI::tr("CUserMail-_from-court") . "&nbsp;: <span>" . CMbString::htmlSpecialChars($forward->from) . "</span><br>" . CAppUI::tr(
                "CUserMessageDest-datetime_sent"
            ) . ": " . CMbDT::format($forward->date_inbox, '%A %d %B %Y à %Hh%M') . "<br />" . CAppUI::tr(
                "CUserMessage-subject"
            ) . ": " . $forward->subject . "<br>" . CAppUI::tr(
                "CUserMessageDest-to_user_id-court"
            ) . ": " . CMbString::htmlSpecialChars($forward->to) . "</div><br /><br />";

        if ($forward->text_html_id) {
            $forward->loadContentHTML();
            $mail->_content .= $forward->_text_html->content;
        } elseif ($forward->text_plain_id) {
            $forward->loadContentPlain();
            $mail->_content .= $forward->_text_plain->content;
        }
    }

    $mail->store();

    if ($forward_mail) {
        $forward->loadAttachments();
        foreach ($forward->_attachments as $_attachment) {
            $file = $_attachment->loadFiles();

            if ($file && $file->_id) {
                $attachment   = new CMailAttachments();
                [$type, $subtype] = explode('/', $file->file_type);
                $attachment->type    = $attachment->getTypeInt($type);
                $attachment->part    = 1;
                $attachment->subtype = $subtype;
                $attachment->bytes   = $file->doc_size;
                [$file_name, $extension] = explode('.', $file->file_name);
                $attachment->name        = $file_name;
                $attachment->extension   = $extension;
                $attachment->mail_id     = $mail->_id;
                $attachment->disposition = 'ATTACHMENT';
                $attachment->store();

                $file_attachment               = new CFile();
                $file_attachment->object_id    = $attachment->_id;
                $file_attachment->object_class = $attachment->_class;
                $file_attachment->author_id    = CAppUI::$user->_id;
                $file_attachment->file_name    = $attachment->name;
                $file_attachment->file_date    = CMbDT::dateTime();
                $file_attachment->fillFields();
                $file_attachment->updateFormFields();
                $file_attachment->doc_size  = $attachment->bytes;
                $file_attachment->file_type = $file->file_type;
                $file_attachment->setContent(file_get_contents($file->_file_path));

                if ($msg = $file_attachment->store()) {
                    CAppUI::setMsg(
                        CAppUI::tr('CMailAttachments-error-upload-file') . ':' . CAppUI::tr($msg),
                        UI_MSG_ERROR
                    );
                    CApp::rip();
                }

                $attachment->file_id = $file->_id;
                if ($msg = $attachment->store()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                    CApp::rip();
                }
            }
        }
    }
}
$mail->loadAttachments();
foreach ($mail->_attachments as $_attachment) {
    $_attachment->loadFiles();
}

if ($contact_support_ox) {
    $mail->to      = CAppUI::gconf("oxCabinet General email_support");
    $mail->subject = $mail_subject;
}

// Initialisation de CKEditor
$templateManager              = new CTemplateManager();
$templateManager->editor      = "ckeditor";
$templateManager->messageMode = true;
$templateManager->initHTMLArea();

$smarty = new CSmartyDP();
$smarty->assign('mail', $mail);
$smarty->assign('account', $account);
$smarty->display('inc_edit_usermail.tpl');
