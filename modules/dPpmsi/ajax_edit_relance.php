<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Pmsi\CRelancePMSI;

$relance_id = CView::get("relance_id", "ref class|CRelancePMSI");
$sejour_id  = CView::get("sejour_id" , "ref class|CSejour");

CView::checkin();

$relance = new CRelancePMSI();
$relance->load($relance_id);

if (!$relance->_id) {
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $relance->sejour_id  = $sejour->_id;
  $relance->patient_id = $sejour->patient_id;
  $relance->chir_id    = $sejour->praticien_id;
  $relance->datetime_creation = "current";
}

$relance->loadRefSejour();
$relance->loadRefChir();
$relance->loadRefPatient();

$smarty = new CSmartyDP();

$smarty->assign("relance", $relance);

$smarty->display("inc_edit_relance");