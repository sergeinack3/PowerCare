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
use Ox\Mediboard\PlanningOp\CModeDestinationSejour;
use Ox\Mediboard\PlanningOp\CModePECSejour;

CCanDo::checkAdmin();
// Récupération des paramètres
$type         = CView::get("type", "str default|pec");
$mode_pec_id  = CView::get("mode_pec_id", "ref class|CModePECSejour");
$mode_dest_id = CView::get("mode_dest_id", "ref class|CModeDestinationSejour");
CView::checkin();

if ($type == "pec") {
  $mode_pec_dest = new CModePECSejour();
  $mode_pec_dest->load($mode_pec_id);
}
else {
  $mode_pec_dest = new CModeDestinationSejour();
  $mode_pec_dest->load($mode_dest_id);
}

if (!$mode_pec_dest->_id) {
  $group = CGroups::loadCurrent();
  $mode_pec_dest->group_id = $group->_id;
}

$smarty = new CSmartyDP();
$smarty->assign("mode_pec_dest", $mode_pec_dest);
$smarty->assign("type"         , $type);
$smarty->display("vw_edit_mode_pec_destination");