<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;

CCanDo::checkEdit();

$protocole_operatoire_id = CView::post("protocole_operatoire_id", "ref class|CProtocoleOperatoire");
$operation_id            = CView::post("operation_id", "ref class|COperation");

CView::checkin();

$materiel_operatoire = new CMaterielOperatoire();
$materiel_operatoire->operation_id = $operation_id;
$materiel_operatoire->protocole_operatoire_id = $protocole_operatoire_id;

foreach ($materiel_operatoire->loadMatchingList() as $_materiel_operatoire) {
  $msg = $_materiel_operatoire->delete();

  CAppUI::setMsg($msg ? : "CMaterielOperatoire-msg-delete", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();
