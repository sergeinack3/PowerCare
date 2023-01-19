<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkEdit();
$patient_id = CView::get("patient_id", "ref class|CPatient", true);
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);
$patient->loadRefMedecinTraitant();

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("in_dhe" , 1);
$smarty->display("vw_maj_email_tel_patient.tpl");
