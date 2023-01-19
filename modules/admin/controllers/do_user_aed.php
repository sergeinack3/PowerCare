<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CUser;

CCanDo::check();

$params = AntiCsrf::validatePOST();

CView::checkin();

$mb_user = new CUser();
$mb_user->bind($params);

$itself = (CUser::get()->_id == $mb_user->user_id);

// if user not itself, must have admin rights on module and edit rights on object
if (!$itself) {
    CCanDo::checkAdmin();
    $mb_user->needsEdit();
}

if (isset($params['del']) && $params['del']) {
    if ($msg = $mb_user->delete()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
    } else {
        CAppUI::setMsg('CUser-msg-delete', UI_MSG_OK);
    }
} elseif ($msg = $mb_user->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
} else {
    $msg = 'CUser-msg-create';
    if (isset($params['user_id']) && $params['user_id']) {
        $msg = 'CUser-msg-modify';
    }

    CAppUI::setMsg($msg, UI_MSG_OK);
}

echo CAppUI::getMsg();

if (isset($params['callback']) && $params['callback']) {
    CAppUI::callbackAjax($params['callback']);
}

CApp::rip();
