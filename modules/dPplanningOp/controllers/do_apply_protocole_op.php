<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;

CCanDo::checkEdit();

$materiels_operatoires = CView::post("materiels_operatoires", "str");
$operation_id          = CView::post("operation_id", "ref class|COperation");

CView::checkin();

$materiel_operatoire = new CMaterielOperatoire();

$where = [
  "materiel_operatoire_id" => CSQLDataSource::prepareIn(array_keys($materiels_operatoires))
];

foreach ($materiel_operatoire->loadList($where) as $_materiel_operatoire) {
  $_materiel_operatoire->_id = "";
  $_materiel_operatoire->operation_id = $operation_id;

  $msg = $_materiel_operatoire->store();

  CAppUI::setMsg($msg ? : "CMaterielOperatoire-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();