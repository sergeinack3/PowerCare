<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$patient_id   = CValue::get('patient_id');
$context_guid = CValue::get('context_guid');

$context = null;
if ($context_guid) {
  $context = CMbObject::loadFromGuid($context_guid);
}

$host = CConstantesMedicales::guessHost($context);

if ($patient_id) {
  $patient = new CPatient();
  $patient->load($patient_id);
}
elseif ($context instanceof CPatient) {
  $patient = $context;
  $context = null;
}
elseif ($context instanceof CMbObject) {
  $patient = $context->loadRefPatient();
}

$where = array();
if ($context) {
  if ($context instanceof CSejour) {
    $whereOr   = array();
    $whereOr[] = "(context_class = '$context->_class' AND context_id = '$context->_id')";
    foreach ($context->_ref_consultations as $_ref_consult) {
      $whereOr[] = "(context_class = '$_ref_consult->_class' AND context_id = '$_ref_consult->_id')";
    }
    if ($context->_ref_consult_anesth) {
      $consult   = $context->_ref_consult_anesth->loadRefConsultation();
      $whereOr[] = "(context_class = '$consult->_class' AND context_id = '$consult->_id')";
    }
    $where[] = implode(" OR ", $whereOr);
  }
  else {
    $where['context_class'] = " = '$context->_class'";
    $where['context_id']    = " = $context->_id";
  }
}

$where['patient_id'] = " = $patient->_id";

/** @var CConstantesMedicales[] $list_constantes */
$constantes      = new CConstantesMedicales();
$list_constantes = $constantes->loadList($where, 'datetime DESC');
foreach ($list_constantes as $_constant) {
  $_constant->loadRefsComments();
}

$constantes_medicales_grid = CConstantesMedicales::buildGrid($list_constantes, false, true);

$smarty = new CSmartyDP();
$smarty->assign('list_constantes', $list_constantes);
$smarty->assign('constantes_medicales_grid', $constantes_medicales_grid);
$smarty->assign('patient', $patient);
$smarty->assign('full_size', 1);
$smarty->assign('view', 'display_all_constantes_patient');
$smarty->display('print_constantes_vert.tpl');
