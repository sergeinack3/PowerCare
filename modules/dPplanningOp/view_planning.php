<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$operation = new COperation();
$sejour    = new CSejour();

if ($sejour_id = CValue::get("sejour_id")) {
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $sejour->loadNDA();
  $sejour->loadRefsFwd();
  $patient =& $sejour->_ref_patient;
  $patient->loadRefs();
  $patient->loadRefTuteur();
  $patient->loadRefsPatientHandicaps();
  $patient_insnir = $patient->loadRefPatientINSNIR();
  $patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

  // Si le modèle est redéfini, on l'utilise
  $model = CCompteRendu::getSpecialModel($sejour->_ref_praticien, "COperation", "[FICHE DHE]", $sejour->group_id, true);

  if ($model->_id) {
    CCompteRendu::streamDocForObject($model, $sejour, $model->factory);
  }
}

if ($operation_id = CValue::get("operation_id")) {
  $operation->load($operation_id);
  $operation->loadRefsFwd();
  $sejour = $operation->_ref_sejour;
  $operation->_ref_sejour->loadRefsFwd();
  $operation->_ref_sejour->loadNDA();
  $patient =& $operation->_ref_sejour->_ref_patient;
  $patient->loadRefs();
  $patient->loadRefTuteur();
  $patient->loadRefsPatientHandicaps();
  $patient_insnir = $patient->loadRefPatientINSNIR();
  $patient_insnir->createDatamatrix($patient_insnir->createDataForDatamatrix());

  // Si le modèle est redéfini, on l'utilise
  $model = CCompteRendu::getSpecialModel($sejour->_ref_praticien, "COperation", "[FICHE DHE]", $sejour->group_id, true);

  if ($model->_id) {
    CCompteRendu::streamDocForObject($model, $operation, $model->factory);
  }
}

$today = CMbDT::date();

$group = CGroups::loadCurrent();
$group->loadConfigValues();
$simple_DHE = $group->_configs['dPplanningOp_COperation_DHE_mode_simple'];

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("operation" , $operation);
$smarty->assign("sejour"    , $sejour   );
$smarty->assign("today"     , $today    );
$smarty->assign("simple_DHE", $simple_DHE);

$smarty->display("view_planning");
