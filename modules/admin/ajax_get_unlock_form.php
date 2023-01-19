<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$user_id     = CView::getRefCheckEdit('user_id', 'ref class|CUser notNull');
$auto_submit = CView::get('auto_submit', 'bool');

CView::checkin();

$auto_submit = ($auto_submit == '1');

$user = CUser::findOrFail($user_id);

$token = AntiCsrf::prepare()
    ->addParam('user_id', $user->_id)
    ->getToken();

$smarty = new CSmartyDP();
$smarty->assign('token', $token);
$smarty->assign('user', $user);
$smarty->assign('auto_submit', $auto_submit);
$smarty->display('vw_unlock_user_form');
