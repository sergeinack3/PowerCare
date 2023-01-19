<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$operation_id = CView::get("operation_id", "ref class|COperation");
$timing       = CView::get("timing", "str");

CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("timing"   , $timing);

$smarty->display("inc_content_top_horaire");