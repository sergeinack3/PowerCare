<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\CPatient;

$patient_id = CView::get("patient_id_api", "ref class|CPatient", true);
CView::checkin();

$patient = new CPatient();
if ($patient_id) {
  $patient->load($patient_id);
}

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("releve", new CConstantReleve());
$smarty->display("vw_dashboard.tpl");
