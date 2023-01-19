<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$operation_id = CView::get("operation_id", 'ref class|COperation');
$type         = CView::get("type", "str default|perop");
CView::checkin();

$interv = new COperation;
$interv->load($operation_id);

CAccessMedicalData::logAccess($interv);

switch ($type) {
  default:
    $type = "perop";
  case "perop":
    $pack = $interv->loadRefGraphPack();
    break;
  case "sspi":
    $pack = $interv->loadRefGraphPackSSPI();
    break;
}

$timings = $pack->getTimingValues($interv);

CApp::json($timings);