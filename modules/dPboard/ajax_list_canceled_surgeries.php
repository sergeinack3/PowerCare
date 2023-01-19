<?php
/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();

$practitioner_id = CView::get("practitioner_id", "ref class|CMediusers");
$function_id     = CView::get("function_id", "ref class|CFunctions");
$date            = CView::get("date", "date default|now");

CView::checkin();

$surgery   = new COperation();
$surgeries = [];
if ($practitioner_id) {
    $practitioner = CMediusers::findOrFail($practitioner_id);

    $surgery->chir_id = $practitioner->_id;
    $surgery->date    = $date;
    $surgery->annulee = 1;
    $surgeries        = $surgery->loadMatchingListEsc();
} else {
    $function = CFunctions::findOrFail($function_id);

    $ds = $surgery->getDS();

    $where = [
        "functions_mediboard.function_id" => $ds->prepare("= ?", $function->_id),
        "operations.date"                 => $ds->prepare("= ?", $date),
        "operations.annulee"              => $ds->prepare("= ?", 1),
    ];
    $ljoin = [
        "users_mediboard"     => "users_mediboard.user_id = operations.chir_id",
        "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id",
    ];

    $surgeries = $surgery->loadList($where, null, null, null, $ljoin);
}


$users_id = CStoredObject::massLoadFwdRef($surgeries, "chir_id");
CStoredObject::massLoadFwdRef($users_id, "function_id");
CStoredObject::massLoadFwdRef($surgeries, "_patient_id");
CStoredObject::massLoadFwdRef($surgeries, "sejour_id");

foreach ($surgeries as $_surgery) {
    if ($_surgery instanceof COperation) {
        $_surgery->loadRefChir()->loadRefFunction();
        $_surgery->loadRefPatient();
        $_surgery->loadRefSejour();
    }
}

$smarty = new CSmartyDP();
$smarty->assign("surgeries", $surgeries);
$smarty->display("inc_list_canceled_surgeries");
