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
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$mail_id = CView::get("mail_id", "ref class|CUserMail");

CView::checkin();

$user = CMediusers::get();

//pop init

//mail
$mail = new CUserMail();
$mail->load($mail_id);
$mail->loadRefsFwd();
$mail->checkHprim(); //HprimMedecin
$mail->checkApicrypt(); //HprimMedecin

//pop account
$log_pop = new CSourcePOP();
$log_pop->load($mail->account_id);

//if not read email, send the seen flag to server
if (!$mail->date_read && !CAppUI::pref("markMailOnServerAsRead") && !$mail->sent) {
  $pop = new CPop($log_pop);
  $pop->open();
  $pop->setFlag($mail->uid, "\\Seen");
  $pop->close();
}
$mail->date_read = CMbDT::dateTime();
$mail->store();

//get the CFile attachments
$nbAttachPicked = 0;
$nbAttach = count($mail->_attachments);
foreach ($mail->_attachments as $_att) {
  $_att->loadRefsFwd();
  if ($_att->_file->_id) {
    $nbAttachPicked++;
  }
}

$mail->checkInlineAttachments();

$headers = preg_split("/(\r\n|\n)/", $mail->_text_plain->content);

//hprim
if ($mail->is_apicrypt || $mail->_is_hprim) {
  $length = $mail->is_apicrypt ? 13 : 12;

  if (count($headers) > $length) {
    $hprim                      = array_slice($headers, 0, $length);
    $content                    = implode("\n", array_splice($headers, $length));
    $mail->_text_plain->content = $content;
  }
}

//Smarty
$smarty = new CSmartyDP();
$smarty->assign("mail", $mail);
$smarty->assign("nbAttachPicked", $nbAttachPicked);
$smarty->assign("nbAttachAll",  $nbAttach);
$smarty->assign("header", $headers);
$smarty->display("vw_open_external_email.tpl");