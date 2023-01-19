<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Liste des naissances pour une intervention donnée
 */

CCanDo::checkRead();

$operation_id = CView::get("operation_id", "ref class|COperation");
$sejour_id    = CView::get("sejour_id", "ref class|CSejour");

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

if ($sejour_id) {
  $sejour = new CSejour();
  $sejour->load($sejour_id);
}
else {
  $sejour = $operation->loadRefSejour();
}

CAccessMedicalData::logAccess($sejour);
CAccessMedicalData::logAccess($operation);

$grossesse  = $sejour->loadRefGrossesse();
$naissances = $sejour->loadRefsNaissances();

/** @var  $naissances CStoredObject[] */
$sejours = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
CStoredObject::massLoadFwdRef($sejours, "patient_id");

$count_by_naissance = array();

/** @var  $naissances CNaissance[] */
foreach ($naissances as $_naissance) {
  $_naissance->loadRefSejourEnfant()->loadRefPatient();
  @$count_by_naissance[$_naissance->sejour_enfant_id]++;
}

$doublons = array();

foreach ($count_by_naissance as $sejour_enfant_id => $_doublon) {
  if ($_doublon != 2) {
    continue;
  }
  foreach ($naissances as $_naissance) {
    if ($_naissance->sejour_enfant_id == $sejour_enfant_id) {
      $doublons[$sejour_enfant_id][] = $_naissance;
    }
  }
}

$grossesse->datetime_cloture = $grossesse->datetime_cloture ?: "now";

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("grossesse", $grossesse);
$smarty->assign("operation", $operation);
$smarty->assign("doublons", $doublons);
$smarty->assign("naissances", $naissances);
$smarty->assign("sejour", $sejour);

$smarty->display("inc_vw_naissances.tpl");
