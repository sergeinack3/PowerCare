<?php

/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkRead();
$operation_id    = CView::get("operation_id", "ref class|COperation", true);
$date            = CView::get("date", "date default|" . CMbDT::date(), true);
$modif_operation = CCanDo::edit() || $date >= CMbDT::date();
$prefix_form     = CView::get("prefix_form", "str");
$type            = CView::get("type", "str default|perop");
$show_cormack    = CView::get("show_cormack", "num default|1");
$complete_view   = CView::get("complete_view", "bool default|0");
CView::checkin();

$operation  = new COperation();
$protocoles = [];
$anesth_id  = "";

$consult_anesth = new CConsultAnesth();
if ($operation->load($operation_id)) {
    CAccessMedicalData::logAccess($operation);

    $operation->loadRefPatient()->loadRefLatestConstantes();
    $operation->_ref_sejour->loadRefGrossesse();
    $operation->loadRefAnesth();
    $operation->_ref_sejour->_ref_patient->loadRefDossierMedical();

    $consult_anesth = $operation->loadRefsConsultAnesth();
    if (!$consult_anesth->_id) {
        $consult_anesth = $operation->_ref_sejour->loadRefsConsultAnesth();
    }

    $consult_anesth->loadRefChir();

    // Affectation de personnel
    $operation->loadAffectationsPersonnel();
    $affectations_personnel = $operation->_ref_affectations_personnel;

    $affectations_operation = array_merge(
        $affectations_personnel["sagefemme"],
        $affectations_personnel["aux_puericulture"],
        $affectations_personnel["aide_soignant"]
    );

    // Récupérer le dernier score de Cormack
    if (!$consult_anesth->cormack) {
        $patient_id = $operation->_ref_patient->patient_id;
        $consult_anesth->getLastCormackValues($patient_id);
    }
}

// Chargement des praticiens
$listAnesths = new CMediusers();
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

$listPers = [
    "sagefemme"        => CPersonnel::loadListPers("sagefemme"),
    "aux_puericulture" => CPersonnel::loadListPers("aux_puericulture"),
    "aide_soignant"    => CPersonnel::loadListPers("aide_soignant"),
];

$currUser = CMediusers::get();
$allow_edit_sortie_salle = ( $currUser->_id ? CAppUI::loadPref('allow_edit_timing_sortie_salle', $currUser->_id) : false) && CCanDo::admin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("listAnesthType", $listAnesthType);
$smarty->assign("listAnesths", $listAnesths);
$smarty->assign("selOp", $operation);
$smarty->assign("date", $date);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("anesth_id", $anesth_id);
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->assign("prefix_form", $prefix_form);
$smarty->assign("show_cormack", $show_cormack);
$smarty->assign("listPers", $listPers);
$smarty->assign("affectations_operation", $affectations_operation);
$smarty->assign("type", $type);
$smarty->assign("last_file_anesthesia", $operation->getLastFileAnesthesia());
$smarty->assign("complete_view", $complete_view);
$smarty->assign("allow_edit_sortie_salle", $allow_edit_sortie_salle);
$smarty->display("inc_vw_anesth");
