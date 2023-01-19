<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
$light     = CView::get("light", "bool default|0");
CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefPatient();
$sejour->countRDVExternes();
$rdv_externes = $sejour->loadRefsRDVExternes();

foreach ($rdv_externes as $_rdv) {
  $_rdv->countDocItems();
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);

if ($light) {
  $smarty->display("inc_vw_rdv_externe_light");
}
else {
  $smarty->display("inc_vw_rdv_externe");
}

