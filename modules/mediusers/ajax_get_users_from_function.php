<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$function_id = CView::get('function_id', 'ref class|CFunctions notNull');

CView::checkin();

$function = CFunctions::loadFromGuid("CFunctions-{$function_id}");
$function->loadRefsUsers();

$users = array();
foreach ($function->_ref_users as $user) {
  $users[] = array(
    'id'    => $user->_id,
    'view'  => $user->_view
  );
}

CApp::json($users);