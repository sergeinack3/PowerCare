<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CMailAttachments;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$user = CMediusers::get();
$mail_id = CView::get("mail_id", 'ref class|CUserMail');
$attachment_id = CView::get("attachment_id", 'ref class|CMailAttachments');

CView::checkin();

//load email
$mail = new CUserMail();
$mail->load($mail_id);

//connection log
$log_pop = new CSourcePOP();
$log_pop->_id = $mail->account_id;
$log_pop->loadMatchingObject();
$pop = new CPop($log_pop);
$pop->open();

$attachments = array();
if ($attachment_id != 0) {
  //load attachment
  $attachment = new CMailAttachments();
  $attachment->load($attachment_id);
  $attachments[] = $attachment;
}
else {
  $mail->loadRefsFwd();
  $attachments = $mail->_attachments;
}

$mail->attachFiles($attachments, $pop, true, true);

$pop->close();

echo CAppUI::getMsg();