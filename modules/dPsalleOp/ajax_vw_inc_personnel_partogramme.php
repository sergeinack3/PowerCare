<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$operation_id = CView::get("operation_id", "ref class|COperation");
$modif_operation = CView::get("modif_operation", "bool");

CView::checkin();
$operation       = new COperation();

if ($operation->load($operation_id)) {
    CAccessMedicalData::logAccess($operation);

    // Affectation de personnel
    $operation->loadAffectationsPersonnel();
    $affectations_personnel = $operation->_ref_affectations_personnel;

    $affectations_operation = array_merge(
        $affectations_personnel["sagefemme"],
        $affectations_personnel["aux_puericulture"],
        $affectations_personnel["aide_soignant"]
    );
}

$listPers = [
    "sagefemme"        => CPersonnel::loadListPers("sagefemme"),
    "aux_puericulture" => CPersonnel::loadListPers("aux_puericulture"),
    "aide_soignant"    => CPersonnel::loadListPers("aide_soignant"),
];

foreach ($affectations_operation as $affectation_personnel) {
    foreach ($listPers as $personnel_type => $personnels) {
        foreach ($personnels as $personnel) {
            if ($personnel) {
                if ($personnel->_id == $affectation_personnel->personnel_id) {
                    unset($listPers[$personnel_type][$personnel->_id]);
                }
            }
        }
    }
}

$smarty = new CSmartyDP();
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("selOp", $operation);
$smarty->assign("listPers", $listPers);
$smarty->assign("affectations_operation", $affectations_operation);
$smarty->display("inc_vw_personnel_partogramme");
