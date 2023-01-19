<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

$purge_start_date = CValue::post('purge_start_date', CMbDT::date());
$purge_limit      = CValue::post('purge_limit');
$practitioner_id  = CValue::post('practitioner_id');
$just_count       = CValue::post('just_count');

if (!$purge_start_date) {
  CAppUI::stepAjax("common-error-You must select a start date", UI_MSG_ERROR);
}

$purge_limit = ($purge_limit) ? $purge_limit : 100;

$ds       = CSQLDataSource::get('std');
$plage_op = new CPlageOp();

$ljoin = array(
  'operations' => '`plagesop`.`plageop_id` = `operations`.`plageop_id`'
);
$where = array(
  'plagesop.date'         => $ds->prepare('>= ?', $purge_start_date),
  'operations.plageop_id' => 'IS NULL'
);

if ($practitioner_id) {
  $where['plagesop.chir_id'] = $ds->prepare('= ?', $practitioner_id);
}

$count = $plage_op->countList($where, null, $ljoin);

$msg = '%d CPlageOp to be removed.';
if ($count == 1) {
  $msg = 'One CPlageOp to be removed.';
}
elseif (!$count) {
  $msg = 'No CPlageOp to be removed.';
}

CAppUI::stepAjax("CPlageOp-msg-$msg", UI_MSG_OK, $count);

if ($just_count || !$count) {
  CAppUI::js("\$('clean_plage_auto').checked = false");
  CApp::rip();
}

$plages_op = $plage_op->loadList($where, null, $purge_limit, null, $ljoin);

if (!$plages_op) {
  CAppUI::js("\$('clean_plage_auto').checked = false");
  CAppUI::stepAjax("CPlageOp-msg-No CPlageOp to be removed.", UI_MSG_OK);
  CApp::rip();
}

$deleted_plages = 0;
foreach ($plages_op as $_plage_op) {
  if ($msg = $_plage_op->delete()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);

  }
  else {
    CAppUI::setMsg('CPlageOp-msg-delete', UI_MSG_OK);
    $deleted_plages++;
  }
}
CAppUI::setMsg('CPlageOp-msg-%d CPlageOp to be removed.', UI_MSG_OK, $count - $deleted_plages);

echo CAppUI::getMsg();
CApp::rip();