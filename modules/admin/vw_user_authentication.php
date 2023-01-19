<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CUserAuthentication;

CCanDo::checkAdmin();

$auth_id = CView::get('auth_id', 'ref class|CUserAuthentication notNull');

CView::checkin();

$auth = CUserAuthentication::findOrFail($auth_id);

$auth->canDo();

$auth->loadRefUser();
$auth->loadRefUserAgent();
$auth->loadRefPreviousUserAuthentication();

$auth->_session_type = 'expired';
if ($auth->isCurrentlyActive()) {
  $auth->_session_type = 'active';
}

$auth->_user_type = 'human';
if ($auth->_ref_user->isRobot()) {
  $auth->_user_type = 'bot';
}

$smarty = new CSmartyDP();
$smarty->assign('auth', $auth);
$smarty->display('vw_user_authentication');
