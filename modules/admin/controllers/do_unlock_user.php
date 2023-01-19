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

CCanDo::checkAdmin();

$params = AntiCsrf::validatePOST();

CView::checkin();

$user_id = $params['user_id'];

$mb_user = CUser::findOrFail($user_id);
$mb_user->needsEdit();

$mb_user->resetLoginErrorsCounter();

if ($msg = $mb_user->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
} else {
    CAppUI::setMsg('CUser-msg-modify');
}

echo CAppUI::getMsg();

CApp::rip();
