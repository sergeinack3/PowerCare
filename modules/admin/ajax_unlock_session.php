<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;

CCanDo::check();

$password = trim(CView::post("password", "str"));
$lock     = CView::post("lock", "bool");

if ($lock) {
  $_SESSION["locked"] = true;

  CView::checkin();
  return;
}

if (!$password) {
  CAppUI::setMsg("Auth-failed-nopassword", UI_MSG_ERROR);
}

if (!CUser::checkPassword(CUser::get()->user_username, $password)) {
  CAppUI::setMsg("Auth-failed-combination", UI_MSG_ERROR);
}

if ($msg = CAppUI::getMsg()) {
  echo $msg;

  CView::checkin();
  return;
}

CAppUI::callbackAjax("Session.unlock");
$_SESSION["locked"] = false;

CView::checkin();