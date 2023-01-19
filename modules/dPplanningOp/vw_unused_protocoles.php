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
use Ox\Mediboard\PlanningOp\CProtocole;

CCanDo::checkAdmin();

$chir_id     = CView::get("chir_id", "ref class|CMediusers", true);
$function_id = CView::get("function_id", "ref class|CFunctions", true);

CView::checkin();

$protocole              = new CProtocole();
$protocole->chir_id     = $chir_id;
$protocole->function_id = $function_id;

$protocole->loadRefChir();
$protocole->loadRefFunction();

$smarty = new CSmartyDP();

$smarty->assign("protocole", $protocole);

$smarty->display("vw_unused_protocoles");
