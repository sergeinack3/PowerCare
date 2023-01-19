<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkAdmin();

$old_patient_id = CView::get('old_patient_id', 'ref class|CPatient notNull');
$new_patient_id = CView::get('new_patient_id', 'ref class|CPatient notNull');
$backprop_name  = CView::get('backprop_name', 'str notNull');

CView::checkin();

$old_patient = new CPatient();
$old_patient->load($old_patient_id);

$new_patient = new CPatient();
$new_patient->load($new_patient_id);

if (!$old_patient || !$old_patient->_id || !$new_patient || !$new_patient->_id) {
  CAppUI::commonError('CPatient.none');
}

$old_patient->loadIPP();
$new_patient->loadIPP();


$old_patient->loadBackRefs($backprop_name);
$old_patient->countBackRefs($backprop_name);

$backprops = $old_patient->getBackProps();

// Sort the backprops based on their logs. Don't sort tags.
if ($backprop_name == 'identifiants') {
  $merged_backs = array();
}
else {
  $merged_backs = CPatient::checkBackRefsOwner($backprop_name, $old_patient);
}

$smarty = new CSmartyDP();
$smarty->assign('old_patient', $old_patient);
$smarty->assign('new_patient', $new_patient);
$smarty->assign('merged_backs', $merged_backs);
$smarty->assign('back_name', $backprop_name);
$smarty->assign('count', $old_patient->_count[$backprop_name]);
$smarty->assign('back_name', $backprop_name);
$smarty->display('inc_unmerge_backprops.tpl');