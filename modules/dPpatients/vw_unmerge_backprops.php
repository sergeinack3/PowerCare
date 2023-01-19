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
$new_patient_id = CView::get('new_patient_id', 'ref class|CPatient');

CView::checkin();

// backprops we don't want to dispatch
$excluded_backs = array(
  'signatures',
  'logs',
  'identifiants'
);

$new_patient = new CPatient();

// Create the new patient
if (!$new_patient_id) {
  $new_patient->bind($_GET);
  $new_patient->updateFormFields();

  if ($msg = $new_patient->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }

  CAppUI::setMsg("$new_patient->_class-msg-create", UI_MSG_OK);
}
else {
  $new_patient->load($new_patient_id);
}

$old_patient = new CPatient();
$old_patient->load($old_patient_id);

if (!$new_patient || (!$new_patient->_id && $new_patient_id) || !$old_patient || !$old_patient->_id) {
  CAppUI::commonError('CPatient.none');
}

// Count all backrefs from old_patient and exclude the 'excluded_backs'
$old_patient->countAllBackRefs();

$counts      = array();
$empty       = array();
$nb_backrefs = 0;
foreach ($old_patient->_count as $_name => $_count) {
  if (!in_array($_name, $excluded_backs) && $_count > 0) {
    $nb_backrefs    += $_count;
    $counts[$_name] = $_count;
  }
  elseif (!in_array($_name, $excluded_backs) && $_count == 0) {
    $empty[$_name] = 0;
  }
}

$old_patient->makeAllBackSpecs();

$smarty = new CSmartyDP();
$smarty->assign('old_patient', $old_patient);
$smarty->assign('new_patient', $new_patient);
$smarty->assign('exclude', $excluded_backs);
$smarty->assign('counts', $counts);
$smarty->assign('nb_backrefs', $nb_backrefs);
$smarty->assign('empty', $empty);
$smarty->display('vw_unmerge_backprops.tpl');