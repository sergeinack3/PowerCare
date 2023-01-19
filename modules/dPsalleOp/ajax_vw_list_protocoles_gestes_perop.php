<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\SalleOp\CProtocoleGestePerop;

CCanDo::checkEdit();
$filtre              = new CProtocoleGestePerop();
$filtre->user_id     = CView::get("user_id", "ref class|CMediusers");
$filtre->function_id = CView::get("function_id", "ref class|CFunctions");
$page                = CView::get("page", "num default|0");
CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("page", $page);
$smarty->assign("filtre", $filtre);
$smarty->display("inc_vw_list_protocoles_gestes_perop");
