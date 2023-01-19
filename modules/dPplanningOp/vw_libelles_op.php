<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CLiaisonLibelleInterv;

CCanDo::checkEdit();
$operation_id = CValue::getOrSession("operation_id");

$liaison = new CLiaisonLibelleInterv();
$liaison->operation_id = $operation_id;
/** @var CLiaisonLibelleInterv[] $liaisons */
$liaisons = $liaison->loadMatchingList("numero");

foreach ($liaisons as $_liaison) {
  $_liaison->loadRefLibelle();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("operation_id", $operation_id);
$smarty->assign('liaisons'    , $liaisons);
$smarty->assign('liaison'    , $liaison);
$smarty->display("vw_libelles_op");