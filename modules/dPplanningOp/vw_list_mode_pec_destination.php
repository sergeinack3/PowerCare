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
use Ox\Mediboard\PlanningOp\CModeDestinationSejour;
use Ox\Mediboard\PlanningOp\CModePECSejour;

CCanDo::checkAdmin();
$type  = CView::get("type", "str default|pec");
CView::checkin();

if ($type == "pec") {
  $list_modes_pec = CModePECSejour::listModes(false);

  $smarty = new CSmartyDP();
  $smarty->assign("list_modes_pec", $list_modes_pec);
  $smarty->display("CModePECSejour_config");
}
else {
  $list_modes_destination = CModeDestinationSejour::listModes(false);

  $smarty = new CSmartyDP();
  $smarty->assign("list_modes_destination", $list_modes_destination);
  $smarty->display("CModeDestinationSejour_config");
}