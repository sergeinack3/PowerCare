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
use Ox\Mediboard\Messagerie\CUserMail;

/**
 * Toggle favorite 1 or 0 for a specificated email
 */
CCanDo::checkRead();

$mail_id = CView::get("mail_id", 'ref class|CUserMail');

CView::checkin();

$mail = new CUserMail();
$mail->load($mail_id);

if (!$mail->_id) {
  CAppUI::stepAjax("CUserMail-mail-notfound-number%d", UI_MSG_ERROR, $mail->_id);
}

$mail->favorite = ($mail->favorite) ? 0 : 1;
$fav = $mail->favorite;
if ($msg = $mail->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}
else {
  CAppUI::stepAjax("CUserMail-toggle-favorite-$fav", UI_MSG_OK);
}
