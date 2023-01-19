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
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;

CCanDo::checkEdit();
CView::checkin();

$token = new CViewAccessToken();
$token->bind($_POST);

if ($token->user_id) {
    if (CUser::get()->_id != $token->user_id) {
        $mb_user = CUser::findOrFail($token->user_id);
        $mb_user->needsEdit();
    }
}

if (isset($_POST['del']) && $_POST['del']) {
    if ($msg = $token->delete()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
    } else {
        CAppUI::setMsg('CViewAccessToken-msg-delete', UI_MSG_OK);
    }
} elseif ($msg = $token->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
} else {
    $msg = 'CViewAccessToken-msg-create';
    if ($token->view_access_token_id) {
        $msg = 'CViewAccessToken-msg-modify';
    }

    CAppUI::setMsg($msg, UI_MSG_OK);
}

echo CAppUI::getMsg();

CApp::rip();
