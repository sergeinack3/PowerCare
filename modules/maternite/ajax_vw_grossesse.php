<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Onglet d'accouchement pour une parturiente en salle d'opération
 */

$operation_id = CView::get("operation_id", "ref class|COperation");
$with_buttons = CView::get("with_buttons", "bool default|1");
CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$operation->loadRefPlageOp();

$sejour = $operation->loadRefSejour();

$grossesse = $sejour->loadRefGrossesse();
$grossesse->loadRefsSejours();
$grossesse->loadRefsConsultations();
foreach ($grossesse->_ref_consultations as $_consult) {
  $_consult->loadRefPlageConsult();
}

$patient = $operation->loadRefPatient();

$smarty = new CSmartyDP();
$smarty->assign("operation", $operation);
$smarty->assign("with_buttons", $with_buttons);
$smarty->display("inc_vw_grossesse");
