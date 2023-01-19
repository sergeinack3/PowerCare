<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMail;

CCanDo::checkRead();

$mail_id = CView::get('mail_id', 'ref class|CUserMail');

CView::checkin();

$mail = new CUserMail();
$mail->load($mail_id);
$mail->loadAttachments();

foreach ($mail->_attachments as $_attachment) {
  $_attachment->loadFiles();
}

$smarty = new CSmartyDP();
$smarty->assign('attachments', $mail->_attachments);
$smarty->display('inc_mail_attachments.tpl');