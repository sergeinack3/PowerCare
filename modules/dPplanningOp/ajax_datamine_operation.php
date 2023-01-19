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
use Ox\Mediboard\PlanningOp\COperationMiner;

CCanDo::checkAdmin();

/** @var int $limit */
$limit  = CView::get("limit", "num default|1", true);
/** @var bool $remine */
$phase = CView::get("phase", "enum list|mine|remine|postmine default|mine", true);
/** @var string $miner_class */
$miner_class  = CView::get("miner_class", "str", true);
// Important for session board reloading
CView::get("automine", "bool default|0", true);
CView::checkin();

/** @var COperationMiner $miner */
$miner = new $miner_class;
if (!$miner instanceof COperationMiner) {
  trigger_error("Wrong miner class", E_USER_ERROR);
  return;
}

$report = $miner->mineSome($limit, $phase);

CAppUI::stepAjax("Miner: %s. Success mining count is '%s'", UI_MSG_OK, $miner_class, $report["success"]);

if ($report["failure"]) {
  CAppUI::stepAjax("Miner: %s. Failure mining counts is '%s'", UI_MSG_ERROR, $miner_class, $report["failure"]);
}