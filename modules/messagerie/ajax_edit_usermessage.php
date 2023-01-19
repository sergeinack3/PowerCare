<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessage;
use Ox\Mediboard\Messagerie\CUserMessageDest;

CCanDo::checkRead();

$user = CMediusers::get();

$to_id         = CView::get("to_id", 'ref class|CMediusers');
$answer_to_all = CView::get('answer_to_all', 'bool');
$in_reply_to   = CView::get("in_reply_to", 'ref class|CUserMessage');
$forward_mail  = CView::get('forward_mail', 'bool default|0');
$message_id    = CView::getRefCheckEdit("usermessage_id", 'ref class|CUserMessage', true);
$dest_message  = CView::get("usermessage_dest_id", 'ref class|CUserMessageDest');
$subject       = utf8_decode(CView::get('subject', 'str'));

CView::checkin();

// classic case
$usermessage = new CUserMessage();
$usermessage->load($message_id);

if ($dest_message) {
    $dest = new CUserMessageDest();
    $dest->load($dest_message);
    $usermessage = $dest->loadRefMessage();
}

/** @var CUserMessageDest[] $destinataires */
// check if sent
$usermessage->_can_edit = true;
$usermessage->loadRefDestUser();
$destinataires = $usermessage->loadRefDests();
foreach ($destinataires as $_dest) {
    if ($_dest->datetime_sent) {
        $usermessage->_can_edit = false;
    }
    if ($_dest->to_user_id == $user->_id && $usermessage->_id && !$_dest->datetime_read) {
        $_dest->datetime_read = CMbDT::dateTime();
        $_dest->store();
    }
    $_dest->loadRefTo()->loadRefFunction();
    $_dest->loadRefFrom()->loadRefFunction();
}

// last check
if (!$usermessage->_id) {
    $usermessage->creator_id = $user->_id;
    if ($subject) {
        $usermessage->subject = $subject;
    }
    // in reply to
    if ($in_reply_to && !$forward_mail) {
        $reply_to = new CUserMessage();
        $reply_to->load($in_reply_to);
        $usermessage->subject     = "Re: {$reply_to->subject}";
        $usermessage->in_reply_to = $in_reply_to;
        $usermessage->creator_id  = $user->_id;

        $reply_to->loadRefDestUser();
        $reply_to->loadRefCreator();
        $date_sent = CMbDT::dateToLocale($reply_to->_ref_dest_user->datetime_sent);

        if (CAppUI::pref('inputMode') == 'html') {
            $usermessage->content = "<br/><br/>$date_sent, {$reply_to->_ref_user_creator->_view}:
        <br/><span style='color: #444;'>{$reply_to->content}</span>";
        } else {
            $usermessage->content = "\n\n$date_sent, {$reply_to->_ref_user_creator->_view}:\n" . CMbString::htmlToText(
                    $reply_to->content
                );
        }

        if ($answer_to_all) {
            $reply_to->loadRefDests();
            $usermessage->_ref_destinataires = [];
            foreach ($reply_to->_ref_destinataires as $_destinataire) {
                if ($_destinataire->to_user_id != $user->_id) {
                    $dest               = new CUserMessageDest();
                    $dest->to_user_id   = $_destinataire->to_user_id;
                    $dest->from_user_id = $usermessage->creator_id;
                    $dest->loadRefTo()->loadRefFunction();
                    $usermessage->_ref_destinataires[] = $dest;
                }
            }
        }
    }

    if ($forward_mail) {
        $forward = new CUserMessage();
        $forward->load($in_reply_to);

        $usermessage->subject    = "Fwd: {$forward->subject}";
        $usermessage->creator_id = $user->_id;

        $forward->loadRefCreator();
        $forward->loadRefDestUser()->loadRefTo();

        $content = "<br /><br /><div>---------- " . CAppUI::tr('CUserMail-msg-Forwarded message') . " ---------<br />
" . CAppUI::tr(
                "CUserMail-_from-court"
            ) . "&nbsp;: <span>" . $forward->_ref_user_creator->_view . "</span><br>" . CAppUI::tr(
                "CUserMessageDest-datetime_sent"
            ) . ": " . CMbDT::format(
                $forward->_ref_dest_user->datetime_sent,
                '%A %d %B %Y à %Hh%M'
            ) . "<br />" . CAppUI::tr(
                "CUserMessage-subject"
            ) . ": " . $forward->subject . "<br>" . CAppUI::tr(
                "CUserMessageDest-to_user_id-court"
            ) . ": " . $forward->_ref_dest_user->_ref_user_to->_view . "</div><br />";

        if (CAppUI::pref('inputMode') == 'html') {
            $usermessage->content .= "<br/><br/><span style='color: #444;'>{$content} {$forward->content}</span>";
        } else {
            $usermessage->content .= "\n\n" . CMbString::htmlToText(
                    $content . "" . $forward->content
                );
        }
    }

    if ($to_id) {
        $dest                  = new CUserMessageDest();
        $dest->to_user_id      = $to_id;
        $dest->from_user_id    = $usermessage->creator_id;
        $dest->user_message_id = null;
        $dest->loadRefTo()->loadRefFunction();
        $usermessage->_ref_destinataires[] = $dest;
    }
}

$usermessage->loadRefsAttachments();
$usermessage->loadRefCreator()->loadRefFunction();


if (CAppUI::pref('inputMode') == 'html') {
    // Initialisation de CKEditor
    $templateManager               = new CTemplateManager();
    $templateManager->editor       = "ckeditor";
    $templateManager->simplifyMode = true;
    if (!$usermessage->_can_edit) {
        $templateManager->printMode = true;
    }
    $templateManager->initHTMLArea();
}
// smarty
$smarty = new CSmartyDP();
$smarty->assign("usermessage", $usermessage);
$smarty->assign("user", $user);
$smarty->display("inc_edit_usermessage.tpl");
