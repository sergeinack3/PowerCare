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

$operation_id = CView::post("operation_id", "ref class|COperation");

CView::checkin();

$materiel_op = new CMaterielOperatoire();

$where = [
    "operation_id" => "= '$operation_id'",
    "status"       => "IS NULL",
];

/** @var CMaterielOperatoire $_materiel_op */
foreach ($materiel_op->loadList($where) as $_materiel_op) {
    $_dm = $_materiel_op->loadRefDM();

    // Pas de validation des dm stérilisables
    if ($_dm->_id && $_dm->type_usage === 'sterilisable') {
        continue;
    }

    $_materiel_op->status = "ok";

    $msg = $_materiel_op->store();

    CAppUI::setMsg($msg ?: "CMaterielOperatoire-msg-modify", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();
