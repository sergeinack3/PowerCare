<?php 
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();

$sejour_id    = CView::get("sejour_id", "ref class|CSejour");
$operation_id = CView::get("operation_id", "ref class|COperation");
$praticien_id = CView::get("praticien_id", "ref class|CMediusers");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

if (!$operation->_id) {
  $operation->sejour_id = $sejour->_id;
  $operation->chir_id = $praticien_id ?: $sejour->praticien_id;
  $operation->date = CMbDT::date();
  $operation->_time_urgence = CMbDT::transform(CMbDT::time(), null, "%H:00:00");
  $operation->cote = "inconnu";
  $operation->urgence = 1;
  $operation->_time_op = "00:30:00";
}

$operation->loadRefChir();

$date_min = CMbDT::date();
$date_max = CMbDT::date("+3 DAYS");

$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("date_min" , $date_min);
$smarty->assign("date_max" , $date_max);

$smarty->display("inc_add_fast_operation");