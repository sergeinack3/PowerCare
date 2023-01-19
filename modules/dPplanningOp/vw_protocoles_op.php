<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkEdit();

$chir_id     = CView::get("chir_id", "ref class|CMediusers", true);
$function_id = CView::get("function_id", "ref class|CFunctions", true);
$group_id    = CView::get("group_id", "ref class|CGroups", true);

CView::checkin();

$protocole_op = new CProtocoleOperatoire();

if ($group_id) {
  $protocole_op->group_id = $group_id;
}
elseif ($function_id) {
  $protocole_op->function_id = $function_id;
}
elseif ($group_id) {
  $protocole_op->chir_id = $chir_id;
}

$protocole_op->loadRefChir();
$protocole_op->loadRefFunction();
$protocole_op->loadRefGroup();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("protocole_op", $protocole_op);

$smarty->display("vw_protocoles_op");