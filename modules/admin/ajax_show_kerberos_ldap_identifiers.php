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
use Ox\Mediboard\Admin\CUser;

CCanDo::checkAdmin();

$user_id = CView::get('user_id', 'ref class|CUser notNull');

CView::checkin();

$user = CUser::findOrFail($user_id);

$smarty = new CSmartyDP();
$smarty->assign('user', $user);
$smarty->display('inc_user_kerberos_security');