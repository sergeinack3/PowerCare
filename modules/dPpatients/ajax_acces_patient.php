<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient", true);

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

if (!$patient->_id) {
  return;
}

$curr_user = CMediusers::get();

$users = $curr_user->loadUsers(PERM_EDIT, $curr_user->function_id);

unset($users[$curr_user->_id]);

foreach ($users as $_user) {
  $_user->loadRefFunction();
}

$perm_obj = new CPermObject();

$where = array(
  "object_id"    => "= '$patient->_id'",
  "object_class" => "= '$patient->_class'",
  "user_id"      => CSQLDataSource::prepareIn(array_keys($users))
);

$perms_obj = $perm_obj->loadList($where);

$perms_by_user = array();

foreach ($perms_obj as $_perm) {
  $perms_by_user[$_perm->user_id] = $_perm;
}

$perm               = new CPermObject();
$perm->object_class = $patient->_class;
$perm->object_id    = $patient->_id;
$perm->permission   = 0;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("users", $users);
$smarty->assign("perms_by_user", $perms_by_user);
$smarty->assign("perm", $perm);

$smarty->display("inc_acces_patient.tpl");