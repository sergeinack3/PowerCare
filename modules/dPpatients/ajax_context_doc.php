<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$patient_id = CView::get("patient_id", "num pos");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$patient->_ref_operations = array();

// Séjours
/** @var CSejour $_sejour */
foreach ($patient->loadRefsSejours() as $_sejour) {
  $_sejour->loadRefPraticien()->loadRefFunction();
  $patient->_ref_operations = array_merge($patient->_ref_operations, $_sejour->loadRefsOperations());
}

// Interventions
CStoredObject::massLoadFwdRef($patient->_ref_operations, "plageop_id");
/** @var COperation $_operation */
foreach ($patient->_ref_operations as $_operation) {
  $_operation->loadRefPlageOp();
  $_operation->loadRefChir()->loadRefFunction();
}

// Consultations
/** @var CConsultation $_consult */
foreach ($patient->loadRefsConsultations() as $_consult) {
  $_consult->loadRefPlageConsult();
  $_consult->_ref_chir->loadRefFunction();
  $_consult->loadRefsDossiersAnesth();
}

// Evenements patient
/** @var CEvenementPatient $_evenement */
foreach ($patient->loadRefDossierMedical()->loadRefsEvenementsPatient() as $_evenement) {
  $_evenement->loadRefTypeEvenementPatient();
}

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);

$smarty->display("inc_context_doc.tpl");