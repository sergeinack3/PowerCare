<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanSoins\CAdministration;

$operation_id = CView::get("operation_id", 'ref class|COperation', true);

CView::checkin();

// Chargement de l'intervention
$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

// Chargement des gestes operatoires
$operation->loadRefsAnesthPerops();
$operation->loadRefsFwd();

// Chargement des administrations per-op
$operation->loadRefSejour();
$sejour =& $operation->_ref_sejour;
$sejour->loadRefPrescriptionSejour();

$sejour->loadRefPatient();
$administrations = array();

if (CModule::getActive("dPprescription")) {
  $administrations = CAdministration::getPerop($sejour->_ref_prescription_sejour->_id, false, $operation_id);
}

// Chargement des constantes saisies durant l'intervention
$whereConst = array();
$whereConst["datetime"] = "BETWEEN '$operation->_datetime_reel' AND '$operation->_datetime_reel_fin'";

$sejour->loadListConstantesMedicales($whereConst);  

// Tri des gestes et administrations perop par ordre chronologique
$perops = array();
foreach ($administrations as $_administration) {
  $_administration->loadRefsFwd();
  $perops[$_administration->dateTime][$_administration->_guid] = $_administration;
}
foreach ($operation->_ref_anesth_perops as $_perop) {
  $perops[$_perop->datetime][$_perop->_guid] = $_perop;
}

$constantes = array("pouls", "ta_gauche", "frequence_respiratoire", "score_sedation", "spo2", "diurese");
foreach ($sejour->_list_constantes_medicales as $_constante_medicale) {
  foreach ($constantes as $_constante) {
    $perops[$_constante_medicale->datetime][$_constante_medicale->_guid][$_constante] = $_constante_medicale->$_constante;
  }
}

ksort($perops);

$smarty = new CSmartyDP();
$smarty->assign("perops", $perops);
$smarty->assign("operation", $operation);
$smarty->display("print_intervention_anesth.tpl");

