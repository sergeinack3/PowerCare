<?php

/**
 * @package Mediboard\SalleOp
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

CCanDo::checkRead();
$operation_id = CView::get("operation_id", "ref class|COperation");
CView::checkin();


$operation = COperation::findOrFail($operation_id);
$operation->loadRefSejour();
$operation->loadRefSalle();

$operation->sortie_salle = CMbDT::dateTime();

CAccessMedicalData::logAccess($operation);

$smarty = new CSmartyDP();
$smarty->assign("operation", $operation);
$smarty->assign("modif_operation", 1);
$smarty->display("inc_edit_sortie_salle");
