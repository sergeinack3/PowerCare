<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Bloc\CDailySalleOccupation;

CCanDo::checkEdit();

$miner = new CDailySalleOccupation();
CApp::log("unmined", $miner->countUnmined());
CApp::log("un-remined", $miner->countUnremined());
CApp::log("un-postmined", $miner->countUnpostmined());

$smarty = new CSmartyDP();
$smarty->display("inc_mine_salle.tpl");
