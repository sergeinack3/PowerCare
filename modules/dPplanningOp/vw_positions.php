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
use Ox\Mediboard\PlanningOp\CPosition;

CCanDo::checkAdmin();
$show_inactive = CView::get("show_inactive", "bool default|0", true);
$refresh = CView::get("refresh", "bool default|0");
CView::checkin();

//Dans le paramétrage nous affichons les positions de tous les établissement
$positions = CPosition::listPositions(!$show_inactive, false);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("positions", $positions);
$smarty->assign("show_inactive", $show_inactive);

if ($refresh) {
  $smarty->display("vw_list_positions");
}
else {
  $smarty->display("vw_positions");
}
