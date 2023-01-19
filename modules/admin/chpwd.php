<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CUser;

CCanDo::check();
$forceChange  = CView::request("forceChange", "bool");
$lifeDuration = CView::request("lifeDuration", "bool");
CView::checkin();

$user = new CUser();
$user->load(CAppUI::$user->_id);
$user->updateSpecs();
$user->isLDAPLinked();

$password_spec = CAppUI::$user->_specs['_user_password'];
$description   = $password_spec->getLitteralDescription();
$description   = str_replace("'_user_username'", $user->user_username, $description);
$description   = explode('. ', $description);
array_shift($description);
$description = array_filter($description);

$csrf_token = AntiCsrf::prepare()
    ->addParam('callback', ['ChangePwd.goHome', 'Control.Modal.close'])
    ->addParam('old_pwd')
    ->addParam('new_pwd1')
    ->addParam('new_pwd2')
    ->getToken();

$smarty = new CSmartyDP();
$smarty->assign('csrf_token', $csrf_token);
$smarty->assign("pw_spec", $password_spec);
$smarty->assign("user", $user);
$smarty->assign("forceChange", $forceChange);
$smarty->assign("lifeDuration", $lifeDuration);
$smarty->assign("lifetime", $user->conf("password_life_duration"));
$smarty->assign("description", $description);
$smarty->display("change_password_legacy");
