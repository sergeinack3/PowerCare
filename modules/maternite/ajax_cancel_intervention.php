<?php
/**
 * @package Mediboard\Maternite
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

$sejour_id = CView::get("sejour_id", "num");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefsOperations();

foreach ($sejour->_ref_operations as $_op) {
  $_op->loadRefPlageOp();
}

$smarty = new CSmartyDP();

$smarty->assign("sejour", $sejour);
$smarty->assign("operations_ids", array_keys($sejour->_ref_operations));

$smarty->display("inc_cancel_intervention.tpl");