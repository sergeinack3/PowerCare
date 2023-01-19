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
use Ox\Mediboard\Messagerie\CMailAttachments;

CCanDo::checkEdit();

$mail_id = CView::get('mail_id', 'ref class|CUserMail');

CView::checkin();

$attachment = new CMailAttachments();
$attachment->mail_id = $mail_id;

$smarty = new CSmartyDP();
$smarty->assign('attachment', $attachment);
$smarty->display('inc_add_mail_attachment.tpl');