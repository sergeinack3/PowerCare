<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\PlanningOp\CSejour;

$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

$sejour = CSejour::findOrNew($sejour_id);
$sejour->loadRefPatient();

// smarty
$smarty = new CSmartyDP();
$smarty->assign("bris", new CBrisDeGlace());
$smarty->assign("sejour", $sejour);
$smarty->display("inc_vw_form_bris_de_glace");