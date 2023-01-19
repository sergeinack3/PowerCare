<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$patient_id = CView::get("patient_id", "ref class|CPatient");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);
$patient->loadIPP();

$can_merge = CPatient::canMerge();

// Patient liés
$links = array();
foreach ($patient->loadPatientLinks() as $_patient_link) {
  $links[$_patient_link->_ref_patient_doubloon->_id] = $_patient_link->_ref_patient_doubloon;
}

// Doublons suspectés
$siblings = $patient->getSiblings();

// Retrait des patients déjà liés
foreach ($siblings as $_sibling) {
  if (array_key_exists($_sibling->_id, $links)) {
    unset($siblings[$_sibling->_id]);
  }
}

CPatient::massLoadIPP($siblings);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("can_merge", $can_merge);
$smarty->assign("siblings", $siblings);
$smarty->assign("links", $links);

$smarty->display("inc_manage_identity");