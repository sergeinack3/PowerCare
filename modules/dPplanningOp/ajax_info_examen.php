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
$type_examen  = CView::get("type_examen", "str");

CView::checkin();

$op = new COperation();
$op->load($operation_id);

CAccessMedicalData::logAccess($op);

$op->loadRefSejour();

switch ($type_examen) {
    default:
    case 'anapath':
        $op->loadRefLaboratoireAnapath();
        break;
    case 'labo':
        $op->loadRefLaboratoireBacterio();
        break;
    case 'rayons_x':
        $op->loadRefAmpli();
}

$smarty = new CSmartyDP();

$smarty->assign("op", $op);
$smarty->assign("type_examen", $type_examen);

$smarty->display("inc_info_examen");
