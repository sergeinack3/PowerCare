<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$patient_id           = CView::getRefCheckRead('patient_id', 'ref class|CPatient');
$context_guid         = CValue::get('context_guid');
$nb_input_display     = CValue::get('nb_input_display', 2);
$selection            = json_decode(stripslashes(CValue::get('selection', '[]')));
$display_search_field = empty($selection);

CView::checkin();

$context = null;
if ($context_guid) {
    $context = CMbObject::loadFromGuid($context_guid);
}

$host = CConstantesMedicales::guessHost($context);
if ($host instanceof CGroups) {
    $host = CMediusers::get()->loadRefFunction();
}

if ($patient_id) {
    $patient = new CPatient();
    $patient->load($patient_id);
} elseif ($context instanceof CPatient) {
    $patient = $context;
    $context = null;
} elseif ($context instanceof CMbObject) {
    $patient = $context->loadRefPatient();
}
$patient->canDo();
$latest_constantes = $patient->loadRefLatestConstantes(CMbDT::dateTime(), $selection, $context, false);

$where = [];
if (!empty($selection)) {
    $whereOr = [];

    foreach ($selection as $_constant) {
        $whereOr[] = "$_constant IS NOT NULL";
    }

    $where[] = implode(" OR ", $whereOr);
}

if ($context) {
    if ($context instanceof CSejour) {
        $whereOr   = [];
        $whereOr[] = "(context_class = '$context->_class' AND context_id = '$context->_id')";
        foreach ($context->_ref_consultations as $_ref_consult) {
            $whereOr[] = "(context_class = '$_ref_consult->_class' AND context_id = '$_ref_consult->_id')";
        }
        if ($context->_ref_consult_anesth) {
            $consult   = $context->_ref_consult_anesth->loadRefConsultation();
            $whereOr[] = "(context_class = '$consult->_class' AND context_id = '$consult->_id')";
        }
        $where[] = implode(" OR ", $whereOr);
    } else {
        $where['context_class'] = " = '$context->_class'";
        $where['context_id']    = " = $context->_id";
    }
}

$where['patient_id'] = " = $patient->_id";


$limit = "0, $nb_input_display";

$constantes             = new CConstantesMedicales();
$constantes->patient_id = $patient->_id;
$constantes->loadRefPatient();
$constantes->updateFormFields();

if ($context) {
    $constantes->context_id    = $context->_id;
    $constantes->context_class = $context->_class;
    $constantes->loadRefContext();
}

if (empty($selection) || is_null($host)) {
    $constants_ranks = CConstantesMedicales::getConstantsByRank('form', false, $host);
} else {
    $constants_ranks = CConstantesMedicales::selectConstants($selection, 'form', $host);
}

$list_constantes = $constantes->loadList($where, 'datetime DESC', $limit);
foreach ($list_constantes as $_constante) {
    $_constante->loadRefsComments();
}

$smarty = new CSmartyDP();
$smarty->assign('patient_id', $patient_id);
$smarty->assign('patient', $patient);
$smarty->assign('constantes', $constantes);
$smarty->assign('latest_constantes', $latest_constantes);
$smarty->assign('list_constantes', $list_constantes);
$smarty->assign('context', $context);
$smarty->assign('context_guid', $context_guid);
$smarty->assign('constants_ranks', $constants_ranks);
$smarty->assign('selection', $selection);
$smarty->assign('display_search_field', $display_search_field);
$smarty->assign('modal', CValue::get('modal'), 0);
$smarty->display('inc_constantes_table_mode.tpl');
