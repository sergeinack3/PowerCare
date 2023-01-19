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
use Ox\Mediboard\SalleOp\CGestePerop;

CCanDo::checkEdit();
$filtre              = new CGestePerop();
$filtre->user_id     = CView::get("user_id", "num pos", true);
$filtre->function_id = CView::get("function_id", "num pos", true);
$page                = CView::get("page", "num default|0");
$keywords            = CView::get("keywords", "str", true);
CView::checkin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("filtre", $filtre);
$smarty->display("inc_vw_gestes_perop");

