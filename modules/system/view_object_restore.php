<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkAdmin();

$log = new CUserLog();

$log->object_class = CValue::postOrSession("object_class");
$log->date         = CValue::postOrSession("date");
$log->user_id      = CValue::postOrSession("user_id");
$do_it             = CValue::post("do_it");

$user = new CMediusers();
$users = $user->loadGroupList();

foreach ($users as $_user) {
  $_user->loadRefFunction();
}

$classes = CApp::getInstalledClasses(array(), true);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("log", $log);
$smarty->assign("users", $users);
$smarty->assign("classes", $classes);
$smarty->assign("do_it", $do_it);
$smarty->display("view_object_restore.tpl");
