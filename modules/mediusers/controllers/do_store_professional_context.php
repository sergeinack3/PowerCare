<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::check();

/** @var CMediusers $user */
$user = CMediusers::get();

if (!$user || !$user->_id) {
  CAppUI::commonError();
}

$params = array();

foreach ($user::$professional_context_fields as $_field => $_mandatory) {
  $params[$_field] = CView::post($_field, $user->_props[$_field]);
}

$user->bind($params);

// VERY IMPORTANT: In order to prevent password alteration
$user->_user_password = null;

if ($msg = $user->store()) {
  CView::checkin();
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg("{$user->_class}-msg-modify", UI_MSG_OK);

  // Session usage
  CMediusers::setProfessionalContext();
}

CView::checkin();

echo CAppUI::getMsg();
CApp::rip();