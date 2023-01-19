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
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();
// Récupération des paramètres
$see_motif = CView::get("see_motif", "bool default|0");
$sejour_id = CView::get("sejour_id", "ref class|CSejour");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);

if ($see_motif) {
  $smarty->display("inc_motif_sejour");
}
else {
  $sejour->loadRefPatient();
  $smarty->display("vw_edit_motif_sejour");
}
