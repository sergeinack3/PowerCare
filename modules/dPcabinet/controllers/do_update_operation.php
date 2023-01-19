<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\PlanningOp\COperation;

$operation_id      = CValue::post('operation_id');
$consult_anesth_id =  CValue::post('consult_anesth_id');

$operation = new COperation();
$operation->load($operation_id);

$consult_anesth = new CConsultAnesth();
$consult_anesth->load($consult_anesth_id);

$fields = array();

$fields['type_anesth']        = CValue::post('type_anesth', 'COperation');
$fields['ASA']                = CValue::post('ASA', 'COperation');
$fields['position_id']        = CValue::post('position_id', 'COperation');
$fields['rques']              = CValue::post('rques', 'COperation');
$fields['depassement_anesth'] = CValue::post('depassement_anesth', 'COperation');

if (CAppUI::conf('dPplanningOp COperation show_duree_uscpo')) {
  $fields['passage_uscpo']  = CValue::post('passage_uscpo', 'COperation');
  $fields['duree_uscpo']    = CValue::post('duree_uscpo', 'COperation');
}

foreach ($fields as $_field => $_object) {
  if ($_object == 'CConsultAnesth') {
    $operation->$_field = $consult_anesth->$_field;
  }
  else {
    if ($_field != 'type_anesth') {
      $consult_anesth->$_field = $operation->$_field;
    }
  }
}

$consult_anesth->operation_id = $operation_id;

if ($msg = $operation->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

if ($msg = $consult_anesth->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

CAppUI::stepMessage(UI_MSG_OK, 'CConsultAnesth.operation_linked');
CApp::rip();