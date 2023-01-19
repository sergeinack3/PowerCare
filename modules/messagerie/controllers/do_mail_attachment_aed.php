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
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CMailAttachmentController;

CCanDo::checkEdit();

CView::checkin();

CApp::setTimeLimit(600);
ignore_user_abort(1);
ini_set("upload_max_filesize", CAppUI::gconf("dPfiles General upload_max_filesize"));

$do = new CMailAttachmentController();
$do->doBind();
if (intval(CValue::read($do->request, 'del'))) {
  $do->doDelete();
}
else {
  $do->doStore();
}

$smarty = new CSmartyDP;
$messages = CAppUI::getMsg();
$smarty->assign('messages', $messages);
$smarty->assign('closeModal', 1);
$smarty->display('inc_callback_modal.tpl');

$do->doRedirect();