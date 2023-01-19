<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CModelObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CUserAuthentication;
use Ox\Mediboard\System\CUserAuthenticationError;

CCanDo::checkEdit();
$user_id = CView::get("user_id", "ref class|CUser");
CView::checkin();

// Ugly hack in order to rebuild spec at object instantiation
// Needed for CEnumSpec & CSetSpec locales which are not translated but in cache
// because of unserialization of CUserAuth object happens before locales loading (see CModelObject::initialize())
unset(CModelObject::$spec['CUserAuthentication']);

$user_auth               = new CUserAuthentication();
$user_auth->_start_date  = CMbDT::dateTime('-7 days');
$user_auth->_end_date    = CMbDT::dateTime();
$user_auth->_auth_method = implode("|", CUserAuthentication::AUTH_METHODS);

if ($user_id) {
    $user_auth->user_id = $user_id;
}

$user_auth_error = new CUserAuthenticationError();

$smarty = new CSmartyDP("modules/admin");
$smarty->assign("user_auth", $user_auth);
$smarty->assign("user_auth_error", $user_auth_error);
$smarty->display("vw_users_auth");
