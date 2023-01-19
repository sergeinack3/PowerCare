<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;

CCanDo::check();

$user_id       = CView::post("user_id", "ref class|CUser");
$user_password = CView::post("user_password", "str");
$form_name     = CView::post("form_name", "str");
$callback      = CView::post("callback", "str");

CView::checkin();

$user = new CUser();
$user->load($user_id);

if ($user->_id && CUser::checkPassword($user->user_username, $user_password)) {
  CAppUI::callbackAjax($callback, $user_id, $form_name);
}
else {
  CAppUI::setMsg("CUser-user_password-nomatch", UI_MSG_ERROR);
  echo CAppUI::getMsg();
}
