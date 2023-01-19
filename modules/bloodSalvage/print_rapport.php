<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\BloodSalvage\Services\BloodSalvageService;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Personnel\CPersonnel;

$anticoag         = "";
$blood_salvage_id = CValue::get("blood_salvage_id");
$blood_salvage    = new CBloodSalvage();

if ($blood_salvage_id) {
    $blood_salvage->load($blood_salvage_id);
    $blood_salvage->loadrefsFwd();
    $blood_salvage->_ref_operation->loadRefsFwd();
    $blood_salvage->_ref_operation->_ref_anesth->load($blood_salvage->_ref_operation->anesth_id);
    $blood_salvage->_ref_operation->loadRefTypeAnesth();
    $blood_salvage->_ref_operation->loadRefPatient();
    $blood_salvage->_ref_operation->_ref_patient->loadRefs();
    $blood_salvage->_ref_operation->_ref_patient->loadRefDossierMedical();
    $blood_salvage->_ref_operation->_ref_patient->loadRefLatestConstantes();
    $anticoag = "";
    if (CModule::getActive("dPmedicament")) {
        if ($blood_salvage->anticoagulant_cip) {
            $anticoag = CMedicamentProduit::get($blood_salvage->anticoagulant_cip);
        }
    } else {
        $list               = CAppUI::conf("bloodSalvage AntiCoagulantList");
        $anticoagulant_list = explode("|", $list);
        if ($blood_salvage->anticoagulant_cip !== null) {
            $anticoag = $anticoagulant_list[$blood_salvage->anticoagulant_cip];
        }
    }

    $list_nurse_sspi = CPersonnel::loadListPers("reveil");
    $tabAffected     = [];
    $timingAffect    = [];
    BloodSalvageService::loadAffected($blood_salvage->_id, $list_nurse_sspi, $tabAffected, $timingAffect);
    $version_patient = CModule::getActive("dPpatients");
}

$smarty = new CSmartyDP();

$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("tabAffected", $tabAffected);
$smarty->assign("anticoagulant", $anticoag);

$smarty->display("print_rapport.tpl");
