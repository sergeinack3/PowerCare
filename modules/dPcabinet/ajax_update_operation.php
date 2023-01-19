<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\PlanningOp\COperation;

$operation_id      = CValue::get('operation_id');
$consult_anesth_id = CValue::get('consult_anesth_id');

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($consult_anesth_id);

$fields = array(
  'passage_uscpo' => array(
    'object' => 'COperation'
  ),
  'duree_uscpo' => array(
    'object' => 'COperation'
  ),
  'ASA' => array(
    'object' => 'COperation'
  ),
  'position_id' => array(
    'object' => 'COperation'
  ),
  'type_anesth' => array(
    'object' => 'COperation'
  ),
  'rques' => array(
    'object' => 'COperation'
  ),
  'depassement_anesth' => array(
    'object' => 'COperation'
  )
);

$auto_link = true;
foreach ($fields as $_name => &$_field) {
  if (is_null($consult_anesth->$_name) || is_null($operation->$_name)) {
    $_field['status'] = 'warning';
  }
  elseif ($consult_anesth->$_name == $operation->$_name) {
    $_field['status'] = 'ok';
    if ($_name == 'duree_uscpo' && ($consult_anesth->$_name == 0 && $operation->$_name == 0)) {
      $_field['status'] = 'warning';
    }
  }
  else {
    $_field['status'] = 'error';
    $auto_link = false;
  }
  if (is_null($operation->$_name) && !is_null($consult_anesth->$_name)) {
    $_field['object'] = 'CConsultAnesth';
  }
}

if (CAppUI::conf('dPplanningOp COperation show_duree_uscpo') == 0) {
  unset($fields['passage_uscpo']);
  unset($fields['durre_uscpo']);
}

$smarty = new CSmartyDP();
$smarty->assign('consult_anesth', $consult_anesth);
$smarty->assign('operation'     , $operation);
$smarty->assign('fields'        , $fields);
$smarty->assign('auto_link'     , $auto_link);
$smarty->display('inc_consult_anesth/update_operation.tpl');