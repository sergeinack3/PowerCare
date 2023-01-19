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
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;

CCanDo::checkAdmin();
$refresh_mode = CView::get("refresh_mode", "bool default|0");
CView::checkin();

$cpi      = new CChargePriceIndicator();
$list_cpi = $cpi->loadGroupList();

$mode_entree       = new CModeEntreeSejour();
$list_modes_entree = $mode_entree->loadGroupList();

$mode_sortie       = new CModeSortieSejour();
$list_modes_sortie = $mode_sortie->loadGroupList();


$smarty = new CSmartyDP();

$smarty->assign("list_cpi"         , $list_cpi);
$smarty->assign("list_modes_entree", $list_modes_entree);
$smarty->assign("list_modes_sortie", $list_modes_sortie);
$smarty->assign("refresh_mode"     , $refresh_mode);

$smarty->display("vw_parametrage");