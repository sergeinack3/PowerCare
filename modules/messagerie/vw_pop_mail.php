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
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CSourcePOP;

/**
 * open a mail by its UID, directly from server
 */
CCanDo::checkAdmin();

$mail_id = CView::get("id", 'ref class|CUserMail');

CView::checkin();

//usermail
$mail = new CUserMail();
$mail->load($mail_id);
$mail->loadAttachments();

//client POP
$clientPOP = new CSourcePOP();
$clientPOP->load($mail->account_id);
$pop = new CPop($clientPOP);
if (!$pop->open()) {
  return;
}

//overview
$overview = $pop->header($mail->uid);
$msgno = $overview->msgno;

$infos = $pop->infos($msgno);

//structure
$structure = $pop->structure($mail->uid);

//content
$content = $pop->getFullBody($mail->uid);

//attachments
$attachments = array();
$_attachments = $pop->getListAttachments($mail->uid);
foreach ($_attachments as $_attach) {
  $attachments[] = $_attach->getPlainFields();
}

$pop->close();

$smarty = new CSmartyDP();
$smarty->assign("mail", $mail);
$smarty->assign("overview", $overview);
$smarty->assign("structure", $structure);
$smarty->assign("mail_id", $mail_id);
$smarty->assign("content", $content);
$smarty->assign("attachments", $attachments);
$smarty->assign("infos", $infos);
$smarty->display("vw_pop_mail.tpl");