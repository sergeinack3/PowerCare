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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CPosition;

CCanDo::checkAdmin();
$position_id = CView::get("position_id", "ref class|CPosition", true);
CView::checkin();

$position = CPosition::findOrNew($position_id);
if (!$position->_id) {
  $position->group_id = CGroups::get()->_id;
}
// Récupération des groups
$groups = CGroups::loadGroups(PERM_EDIT);

// Creation du template
$smarty = new CSmartyDP();
$smarty->assign("position", $position);
$smarty->assign("groups"  , $groups);
$smarty->display("vw_edit_position");