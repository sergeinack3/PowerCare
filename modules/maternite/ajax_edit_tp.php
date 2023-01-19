<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$dossier_medical = $patient->loadRefDossierMedical();
$dossier_medical->loadRefsTraitements();
$dossier_medical->loadRefPrescription();
if ($dossier_medical->_ref_prescription->_id) {
  foreach ($dossier_medical->_ref_prescription->_ref_prescription_lines as $_line) {
    $_line->loadRefsPrises();
  }
}

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("line", new CPrescriptionLineMedicament());
$smarty->assign("traitement", new CTraitement());

$smarty->display("inc_edit_tp.tpl");
