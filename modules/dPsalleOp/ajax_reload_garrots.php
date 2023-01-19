<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\COperationGarrot;

$operation_id = CView::get('operation_id', 'ref class|COperation');

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

if (!$operation || !$operation->_id) {
  CAppUI::stepAjax('common-error-Unable to load object: %s-%s', UI_MSG_ERROR, 'COperation', $operation_id);
}

$operation->loadGarrots();

$smarty = new CSmartyDP();
$smarty->assign('operation', $operation);
$smarty->assign('garrot', new COperationGarrot());
$smarty->display('vw_garrots.tpl');