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
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CMailAttachments;
use Ox\Mediboard\Messagerie\CMailPartToFile;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CContentAny;
use Ox\Mediboard\System\CContentHTML;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$user         = CMediusers::get();
$objects_guid = explode('|', CView::get('objects', 'str'));
$attach_list  = CView::get('attach_list', 'str');
$text_plain   = CView::get('text_plain_id', 'ref class|CContentAny');
$text_html    = CView::get('text_html_id', 'ref class|CContentHTML');
$rename_text  = CView::get('rename_text', 'str');
$category_id  = CView::get('category_id', 'ref class|CFilesCategory');
$mail_id      = CView::get('mail_id', 'ref class|CUserMail');

CView::checkin();

$mail = new CUserMail();
$mail->load($mail_id);

if (empty($objects_guid)) {
    CAppUI::stepAjax("CUserMail-link-objectNull", UI_MSG_ERROR);
}

if (str_replace("-", "", $attach_list) == "" && !$text_plain && !$text_html) {
    CAppUI::stepAjax("CMailAttachments-msg-no_object_to_attach", UI_MSG_ERROR);
}

$objects = array();
foreach ($objects_guid as $_guid) {
    $_object = CMbObject::loadFromGuid($_guid);
    $objects[] = $_object;
}
if ($_object->_class == 'CPatient') {
    /** @var CPatient $_object */
    $mail->linked_patient_id = $_object->_id;
} elseif ($_object->_class == 'COperation') {
    /** @var COperation $_object */
    $_object->loadRefSejour();
    $mail->linked_patient_id = $_object->_ref_sejour->patient_id;
} else {
    $mail->linked_patient_id = $_object->patient_id;
}

if ($str = $mail->store()) {
    CAppUI::stepAjax($str, UI_MSG_ERROR);
}

$attachment_ids = trim($attach_list) ? explode("-", $attach_list) : array();
foreach ($attachment_ids as $_attachment_id) {
    if (!$_attachment_id) {
        continue;
    }

    $_attachment = new CMailAttachments();
    if ($_attachment_id != "") {
        $_attachment->load($_attachment_id);
        $_attachment->loadRefsFwd();

        if (!$_attachment->_id) {
            continue;
        }
    }

    if (!$_attachment->_file->_id) {
        $account = new CSourcePOP();
        $account->load($mail->account_id);

        $pop = new CPop($account);
        $pop->open();

        $file = new CFile();
        $file->setObject($_attachment);
        $file->author_id = CAppUI::$user->_id;

        $pop = new CPop($account);
        $pop->open();
        $file_pop = $pop->decodeMail($_attachment->encoding, $pop->openPart($mail->uid, $_attachment->getpartDL()));
        $pop->close();

        $file->file_name = $_attachment->name;
        $file->file_type = $_attachment->getType($_attachment->type, $_attachment->subtype);
        $file->fillFields();
        $file->setContent($file_pop);

        if ($str = $file->store()) {
            CAppUI::stepAjax($str, UI_MSG_ERROR);
        } else {
            $_attachment->file_id = $file->_id;
            $_attachment->_file   = $file;
            $_attachment->store();
        }
    } else {
        $file = $_attachment->_file;
    }

    foreach ($objects as $_object) {
        $_file                    = new CFile();
        $_file->file_name         = $file->file_name;
        $_file->file_type         = $file->file_type;
        $_file->file_date         = $file->file_date;
        $_file->author_id         = $file->author_id;
        $_file->language          = $file->language;
        $_file->file_category_id  = $category_id;
        $_file->setObject($_object);
        $_file->fillFields();

        $_file->setContent($file->getBinaryContent());

        if ($msg = $_file->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
            CApp::rip();
        }

        $_link             = new CMailPartToFile();
        $_link->part_id    = $_attachment->_id;
        $_link->part_class = $_attachment->_class;
        $_link->file_id    = $_file->_id;

        if ($msg = $_link->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
            CApp::rip();
        }
    }
}

//text link
if ($text_html || $text_plain) {
    $content_type = "text/plain";

    if ($text_html) {
        $text = new CContentHTML();
        $text->load($text_html);
        $content_type = "text/html";
    } else {
        $text = new CContentAny();
        $text->load($text_plain);
    }

    foreach ($objects as $_object) {
        $_file                   = new CFile();
        $_file->author_id        = CAppUI::$user->_id;
        $_file->file_name        = "sans_titre";
        $_file->file_category_id = $category_id;

        if ($mail->subject) {
            $_file->file_name = $mail->subject;
        }

        if ($rename_text) {
            $_file->file_name = $rename_text;
        }

        $_file->file_type = $content_type;
        $_file->setObject($_object);
        $_file->fillFields();

        if ($mail->is_apicrypt) {
            $text->content = str_replace(["****FIN****", "****FINFICHIER****", "[apicrypt]"], '', $text->content);

            if ($text instanceof CContentAny) {
                $lines = explode("\n", $text->content);
                if (count($lines) > 13) {
                    $text->content = implode("\n", array_splice($lines, 13));
                }
            }
        }

        $_file->setContent(trim($text->content));

        if ($msg = $_file->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
            CApp::rip();
        }

        $_link             = new CMailPartToFile();
        $_link->part_id    = $mail->_id;
        $_link->part_class = $mail->_class;
        $_link->file_id    = $_file->_id;

        if ($msg = $_link->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
            CApp::rip();
        }
    }
}

if (!$text_html && !$text_plain && $attach_list == "") {
    CAppUI::stepAjax("CMailAttachments-msg-noAttachSelected", UI_MSG_ERROR);
} else {
    CAppUI::stepAjax("CUserMail-content-attached", UI_MSG_OK);
}
