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
use Ox\Mediboard\Personnel\CAffectationPersonnel;
use Ox\Mediboard\PlanningOp\COperation;

$operation_id = CView::get("operation_id", "ref class|COperation", true);
$date         = CView::get("date", "date default|now", true);
$in_salle     = CView::get("in_salle", "bool default|1");

CView::checkin();

$modif_operation = CCanDo::edit() || $date >= CMbDT::date();

// Chargement de l'operation selectionnee
$selOp = new COperation();
$selOp->load($operation_id);

CAccessMedicalData::logAccess($selOp);

$selOp->loadRefSejour();
$plageOp = $selOp->loadRefPlageOp();

$listPers = $selOp->loadPersonnelDisponible();

// Creation du tableau d'affectation de personnel
$tabPersonnel = [];

$plageOp->loadAffectationsPersonnel();
$affectations_personnel = $plageOp->_ref_affectations_personnel;

$affectations_plage     = array_merge(
    $affectations_personnel["iade"],
    $affectations_personnel["op"],
    $affectations_personnel["op_panseuse"],
    $affectations_personnel["sagefemme"],
    $affectations_personnel["manipulateur"],
    $affectations_personnel["aux_puericulture"],
    $affectations_personnel["instrumentiste"],
    $affectations_personnel["circulante"],
    $affectations_personnel["aide_soignant"],
    $affectations_personnel["brancardier"]
);

// Tableau de stockage des affectations
$tabPersonnel["plage"]     = [];
$tabPersonnel["operation"] = [];

foreach ($affectations_plage as $key => $affectation_personnel) {
    $affectation = new CAffectationPersonnel();
    $affectation->setObject($selOp);
    $affectation->personnel_id          = $affectation_personnel->personnel_id;
    $affectation->parent_affectation_id = $affectation_personnel->_id;
    $affectation->loadMatchingObject();
    if (!$affectation->_id) {
        $affectation->parent_affectation_id = $affectation_personnel->_id;
    }
    $affectation->loadRefPersonnel();
    $affectation->_ref_personnel->loadRefUser();
    $affectation->_ref_personnel->_ref_user->loadRefFunction();
    $tabPersonnel["plage"][$affectation->personnel_id] = $affectation;
}

// Chargement du de l'operation
$affectations_personnel = $selOp->_ref_affectations_personnel;

$affectations_operation = array_merge(
    $affectations_personnel["iade"],
    $affectations_personnel["op"],
    $affectations_personnel["op_panseuse"],
    $affectations_personnel["sagefemme"],
    $affectations_personnel["manipulateur"],
    $affectations_personnel["aux_puericulture"],
    $affectations_personnel["instrumentiste"],
    $affectations_personnel["circulante"],
    $affectations_personnel["aide_soignant"],
    $affectations_personnel["brancardier"]
);

foreach ($affectations_operation as $key => $affectation_personnel) {
    $personnel = $affectation_personnel->_ref_personnel;
    if ($affectation_personnel->parent_affectation_id) {
        unset($affectations_operation[$key]);
        continue;
    }
    $tabPersonnel["operation"][] = $affectation_personnel;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("selOp", $selOp);
$smarty->assign("tabPersonnel", $tabPersonnel);
$smarty->assign("listPers", $listPers);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("in_salle", $in_salle);

$smarty->display("inc_vw_personnel.tpl");
