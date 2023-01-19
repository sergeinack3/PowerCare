<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CConstantGraph;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$constants_list = CValue::get('constants', '[]');
$selection = json_decode(stripslashes($constants_list));

$patient_id   = CValue::get('patient_id');
$context_guid = CValue::get('context_guid');
$period       = CValue::get('period', 0);
$print        = CView::get("print", "bool default|0");
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$where = array();

$where['patient_id'] = " = $patient->_id";

if ($period) {
  switch ($period) {
    case 'week':
      $where['datetime'] = " > '" . CMbDT::dateTime('-7 days') . "'";
      break;
    case 'month':
      $where['datetime'] = " > '" . CMbDT::dateTime('-1 month') . "'";
      break;
    case 'year':
      $where['datetime'] = " > '" . CMbDT::dateTime('-1 year') . "'";
      break;
    default:
  }
}

$whereOr = array();
foreach ($selection as $_constant) {
  if (strpos($_constant, '_') !== 0) {
    $whereOr[] = "$_constant IS NOT NULL";
  }
  elseif (array_key_exists('formula', CConstantesMedicales::$list_constantes[$_constant])) {
    foreach (CConstantesMedicales::$list_constantes[$_constant]['formula'] as $_compound => $_op) {
      $whereOr[] = "$_compound IS NOT NULL";
    }
  }
  else {
    switch ($_constant) {
      case '_imc':
        $whereOr[] = '`poids` IS NOT NULL';
        $whereOr[] = '`taille` IS NOT NULL';
        break;
      case '_vst':
        $whereOr[] = '`poids` IS NOT NULL';
        break;
      case '_tam':
        $whereOr[] = '`ta` IS NOT NULL';
        break;
      case '_ecpa_total':
        $whereOr[] = '`ecpa_avant` IS NOT NULL';
        $whereOr[] = '`ecpa_apres` IS NOT NULL';
      default:
    }
  }
}

if (!empty($whereOr)) {
  $where[] = implode(' OR ', $whereOr);
}

$activate_choice_blood_glucose_units = CConstantesMedicales::getHostConfig(
    "activate_choice_blood_glucose_units",
    CConstantesMedicales::guessHost(CMbObject::loadFromGuid($context_guid))
) ? true : false;

$constant  = new CConstantesMedicales();
$constants = $constant->loadList($where, 'datetime DESC', null, 'datetime');
$constants = CConstantesMedicales::getConvertUnitGlycemie($constants, false, $activate_choice_blood_glucose_units);

foreach ($constants as $_constant) {
  $_constant->loadRefsComments();
}

$smarty = new CSmartyDP();

if (!empty($constants)) {
  $time = false;
  if ($period) {
    $time = true;
  }

  $graph = new CConstantGraph(CConstantesMedicales::guessHost('all'), $context_guid, false, $time);

  $constants_by_graph = array(
    1 => array(
      $selection
    )
  );

  $graph->formatGraphDatas(array_reverse($constants, true), $constants_by_graph, $activate_choice_blood_glucose_units);

  $smarty->assign('graphs', array(1 => $graph->graphs[1][0]));
  $smarty->assign('min_x_index', $graph->min_x_index);
  $smarty->assign('min_x_value', $graph->min_x_value);
  $smarty->assign('constants', $constants_list);
  $smarty->assign('print', $print);
  if (!$period) {
    $smarty->assign('patient', $patient);
  }
}
else {
  $smarty->assign('msg', CAppUI::tr('CConstantGraph-msg-no_values'));
}

$smarty->display('inc_custom_constants_graph.tpl');
