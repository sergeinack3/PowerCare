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
use Ox\Mediboard\System\CUserLog;

CCanDo::checkAdmin();

$patient_id = CView::get('patient_id', 'ref class|CPatient notNull');
$step       = CView::get('step', 'enum list|old|new notNull');

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

if (!$patient || !$patient->_id) {
  CAppUI::commonError('CPatient.none');
}

// Get the patient fields
$plain_fields = $patient->getPlainFields();
$spec         = $patient->getSpec();

$updated_fields = array();
if ($step == 'old') {
  $patient->loadIPP();

  $ds    = $patient->getDS();
  $log   = new CUserLog();
  $where = array(
    'object_id'    => $ds->prepare('= ?', $patient->_id),
    'object_class' => $ds->prepare('= ?', $patient->_class),
    'type'         => "= 'store'"
  );

  $logs = $log->loadList($where);
  foreach ($logs as $_log) {
    $_log->getOldValues();
    if ($_log->_fields) {
      foreach ($_log->_fields as $_field) {
        if (!isset($updated_fields[$_field]) && $_log->_old_values[$_field]) {
          $updated_fields[$_field] = '';
        }
      }
    }
  }
}

// Delete the patient_id from the fields
unset($plain_fields[$spec->key]);

$smarty = new CSmartyDP();
$smarty->assign('patient', $patient);
$smarty->assign('patient_fields', $plain_fields);
$smarty->assign('updated_fields', $updated_fields);
$smarty->assign('specs', $patient->getSpecs());
$smarty->assign('step', $step);
$smarty->display('inc_vw_unmerge_patient.tpl');