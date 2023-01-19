<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\CCellSaver;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$salle = CValue::getOrSession("salle");
$op    = CValue::getOrSession("op");
$date  = CValue::getOrSession("date", CMbDT::date());

$modif_operation = CCanDo::edit() || $date >= CMbDT::date();
$timing          = [];

$inLivretTherapeutique = CAppUI::conf("bloodSalvage inLivretTherapeutique");

if (CModule::getActive("dPmedicament")) {
    $anticoagulant = new CMedicamentClasseATC();
    if ($inLivretTherapeutique) {
        $anticoagulant_list = $anticoagulant->loadRefProduitsLivret("B01AB");
    } else {
        $anticoagulant->loadRefsProduits("B01AB");
        $anticoagulant_list = $anticoagulant->_ref_produits;
    }
    foreach ($anticoagulant_list as $_anticoag) {
        $_anticoag->loadRefProduit();
    }
} else {
    $list               = CAppUI::conf("bloodSalvage AntiCoagulantList");
    $anticoagulant_list = explode("|", $list);
}

$selOp = new COperation();

if ($op) {
    $selOp->load($op);

    CAccessMedicalData::logAccess($selOp);

    $selOp->loadRefsConsultAnesth();
    $selOp->loadRefSejour()->loadRefPatient();
    $selOp->_ref_sejour->_ref_patient->loadRefDossierMedical();
    $selOp->_ref_sejour->_ref_patient->loadRefLatestConstantes();

    $blood_salvage               = new CBloodSalvage();
    $blood_salvage->operation_id = $op;
    $blood_salvage->loadMatchingObject();
    $timing["_recuperation_start"] = [];
    $max_add_minutes               = CAppUI::gconf("dPsalleOp Timing_list max_add_minutes");
    foreach ($timing as $key => $value) {
        for (
            $i = -CAppUI::gconf(
                "dPsalleOp Timing_list max_sub_minutes"
            ); $i < $max_add_minutes && $blood_salvage->$key !== null; $i++
        ) {
            $timing[$key][] = CMbDT::time("$i minutes", $blood_salvage->$key);
        }
    }
}

/*
 * Liste des cell saver.
 */
$cell_saver      = new CCellSaver();
$list_cell_saver = $cell_saver->loadList();

$smarty = new CSmartyDP();

$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("salle", $salle);
$smarty->assign("selOp", $selOp);
$smarty->assign("date", $date);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("totaltime", "00:00:00");
$smarty->assign("anticoagulant_list", $anticoagulant_list);
$smarty->assign("timing", $timing);
$smarty->assign("list_cell_saver", $list_cell_saver);
$smarty->assign("inLivretTherapeutique", $inLivretTherapeutique);

$smarty->display("inc_bloodSalvage.tpl");
