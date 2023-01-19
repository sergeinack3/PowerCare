<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\COperationMiner;

CCanDo::checkAdmin();

/** @var bool $automine */
$automine  = CView::get("automine", "bool", true);
/** @var int $limit */
$limit  = CView::get("limit", "num default|1", true);
CView::checkin();

$counts = COperationMiner::makeOperationCounts();

$miner_classes = CApp::getChildClasses(COperationMiner::class);
$miners = array();
foreach ($miner_classes as $_class) {
  /** @var COperationMiner $miner */
  $miner = new $_class;
  $miner->loadMatchingObject("date DESC");
  $miner->makeMineCounts();
  $miners[] = $miner;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("counts"  , $counts);
$smarty->assign("miners"  , $miners);
$smarty->assign("automine", $automine);
$smarty->assign("limit"   , $limit);
$smarty->display("datamining_board");
