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
use Ox\Mediboard\Messagerie\CSMimeKeyController;

CApp::setTimeLimit(600);
ignore_user_abort(1);

CCanDo::checkEdit();

CView::checkin();

$do = new CSMimeKeyController();
$do->doBind();
if (intval(CValue::read($do->request, 'del'))) {
  $do->doDelete();
}
else {
  $do->doStore();
}

$smarty = new CSmartyDP;
$smarty->assign('messages', CAppUI::getMsg());
$smarty->assign('source_id', CValue::post('source_id'));
$smarty->display('inc_callback_certificate.tpl');

$do->doRedirect();