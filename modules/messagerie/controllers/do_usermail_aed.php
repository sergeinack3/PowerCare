<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Messagerie\CUserMailController;

$do = new CUserMailController();
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
$smarty->assign('closeModal', CValue::post('_closeModal'));
$smarty->display('inc_callback_modal.tpl');

$do->doRedirect();