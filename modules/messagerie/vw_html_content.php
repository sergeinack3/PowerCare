<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Open a contentHTML from an email_id
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMail;

CCanDo::checkRead();

$mail_id = CView::get("mail_id", 'ref class|CUserMail');

CView::checkin();

$mail = new CUserMail();
$mail->load($mail_id);

if ($mail->_id) {
  $mail->loadRefsFwd();
  $mail->checkInlineAttachments();  //inline attachment
  $mail->checkApicrypt();
  $mail->checkHprim();
}

$headers = preg_split("/(\r\n|\n)/", $mail->_text_html->content);

//hprim
if ($mail->is_apicrypt || $mail->_is_hprim) {
  $length = $mail->is_apicrypt ? 13 : 12;

  $hprim = array_slice($headers, 0, $length);
  $mail->_text_html->content = implode("\n", array_splice($headers, $length));
}

$mail->_text_html->content = CMbString::purifyHTML($mail->_text_html->content);

if (strpos($mail->_text_html->content, '<') === false) {
  echo nl2br($mail->_text_html->content);
}
else {
  echo $mail->_text_html->content;
}
