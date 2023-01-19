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
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Messagerie\CUserMail;

/**
 * List of attachment with radio button
 */
CCanDo::checkRead();

$mail_id = CView::get('mail_id', 'ref class|CUserMail');
$rename  = CView::get('rename', 'bool default|0');

CView::checkin();

$mail = new CUserMail();
$mail->load($mail_id);
$mail->loadRefsFwd();

//load files
foreach ($mail->_attachments as $_att) {
  $_att->loadFiles();
  $_att->loadRefLinkedFiles();
}

$cats = CFilesCategory::getFileCategories();

//check for inline attachment
$mail->checkInlineAttachments();

$smarty = new CSmartyDP();
$smarty->assign("mail"  , $mail);
$smarty->assign("cats"  , $cats);
$smarty->assign("rename", $rename);
$smarty->assign('content_file_name', str_replace(['/', '.', '(', ')', '[', ']'], '', $mail->subject) . '.txt');
$smarty->display("inc_vw_list_attachment");
