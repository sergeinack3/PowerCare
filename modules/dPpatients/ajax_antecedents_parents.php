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
use Ox\Mediboard\Patients\CPatientFamilyLink;

CCanDo::checkRead();
$patient_id    = CView::get("patient_id", "ref class|CPatient");
$context_class = CView::get("context_class", "enum list|CSejour|CPatient");
$context_id    = CView::get("context_id", "ref meta|context_class");
CView::checkin();

$patient_family             = new CPatientFamilyLink();
$patient_family->patient_id = $patient_id;
$patient_family->loadMatchingObject();

$patient       = $patient_family->loadRefPatient();
$first_parent  = $patient_family->loadRefParent1();
$second_parent = $patient_family->loadRefParent2();

$first_parent->loadRefDossierMedical()->loadRefsAntecedents();
$second_parent->loadRefDossierMedical()->loadRefsAntecedents();

if (!$context_id) {
  $context_id = $patient_id;
}

$smarty = new CSmartyDP();
$smarty->assign("patient", $patient);
$smarty->assign("first_parent", $first_parent);
$smarty->assign("second_parent", $second_parent);
$smarty->assign("context_class", $context_class);
$smarty->assign("context_id", $context_id);
$smarty->display("inc_antecedents_parents.tpl");
