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
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMail;

/**
 * Toggle favorite 1 or 0 for a specificated email
 */
CCanDo::checkEdit();

$mail_id = CView::get("mail_id", 'ref class|CUserMail');

CView::checkin();

$mail = new CUserMail();
$mail->load($mail_id);

if (!$mail->_id) {
  CAppUI::stepAjax("CUserMail-mail-notfound-number%d", UI_MSG_ERROR, $mail->_id);
}

$arch = $mail->archived = ($mail->archived) ? 0 : 1;

if (!$mail->date_read) {
  $mail->date_read = CMbDT::dateTime();
}
if ($msg = $mail->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}
else {
  CAppUI::stepAjax("CUserMail-toggle-archive-$arch", UI_MSG_OK);
}
