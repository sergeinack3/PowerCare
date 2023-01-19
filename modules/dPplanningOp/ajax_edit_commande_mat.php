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
use Ox\Mediboard\PlanningOp\CCommandeMaterielOp;

CCanDo::checkEdit();
$commande_id = CValue::get("commande_id");

$commande = new CCommandeMaterielOp();
$commande->load($commande_id);

$commande->loadView();
$op = $commande->_ref_operation;
$op->loadRefChir()->loadRefFunction();
$op->loadRefPlageOp();

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("commande", $commande);

$smarty->display("vw_edit_commande");
