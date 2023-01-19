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
use Ox\Mediboard\Admin\CPermObject;

CCanDo::checkAdmin();

$params = AntiCsrf::validatePOST();

CView::checkin();

$perm_object = new CPermObject();
$perm_object->bind($params);

if (isset($params['del']) && $params['del']) {
    if ($msg = $perm_object->delete()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
    } else {
        CAppUI::setMsg('CPermObject-msg-delete', UI_MSG_OK);
    }
} elseif ($msg = $perm_object->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
} else {
    $msg = 'CPermObject-msg-create';
    if (isset($params['perm_object_id']) && $params['perm_object_id']) {
        $msg = 'CPermObject-msg-modify';
    }

    CAppUI::setMsg($msg, UI_MSG_OK);
}

echo CAppUI::getMsg();

if (isset($params['callback']) && $params['callback']) {
    CAppUI::callbackAjax($params['callback']);
}

CApp::rip();
