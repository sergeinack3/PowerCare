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
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient");
$type       = CView::get("type", "str");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$dossier_medical = $patient->loadRefDossierMedical(false);

if ($type) {
  $dossier_medical->loadRefsAntecedentsOfType($type);
}
else {
  $dossier_medical->loadRefsAntecedents(false, false, true);
  $dossier_medical->_ref_antecedents_by_type[$type] = (!isset($dossier_medical->_ref_antecedents_by_type[$type])) ?
    array() :
    $dossier_medical->_ref_antecedents_by_type[$type];
}

$smarty = new CSmartyDP();

$smarty->assign("antecedent", new CAntecedent());
$smarty->assign("antecedents", $dossier_medical->_ref_antecedents_by_type[$type]);
$smarty->assign("patient", $patient);
$smarty->assign("type", $type);

$smarty->display("inc_edit_atcd.tpl");
