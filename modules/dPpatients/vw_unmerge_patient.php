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

$patient_id = CView::get('patient_id', 'ref class|CPatient notNull');

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

if (!$patient || !$patient->_id) {
  CAppUI::commonError('CPatient.none');
}

$smarty = new CSmartyDP();
$smarty->assign('patient', $patient);
$smarty->display('vw_unmerge_patient.tpl');