<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();
$patient_id      = CView::get("patient_id", "ref class|CPatient");
$consultation_id = CView::get("consultation_id", "ref class|CConsultation");
$is_anesth       = CView::get("is_anesth", "bool default|1");
$show_dhe_ecap_config = CModule::getActive('ecap') ? CAppUI::gconf('ecap Display show_buttons_dhe_ecap') : 1;
$show_dhe_ecap   = CView::get("show_dhe_ecap", "bool default|$show_dhe_ecap_config");
$chir_id         = CView::get("chir_id", "ref class|CMediusers");
$mode_cabinet    = CView::get('mode_cabinet', 'bool');
CView::checkin();

$group       = CGroups::loadCurrent();
$multi_group = CAppUI::gconf("dPpatients sharing multi_group");

$patient = CPatient::findOrNew($patient_id);

$where = [
    "group_id" => "= '" . $group->_id . "'",
    "annule"   => "= '0'",
];

foreach ($patient->loadRefsSejours($where) as $_sejour) {
    foreach ($_sejour->loadRefsOperations(["annulee" => "= '0'"]) as $_operation) {
        $_operation->loadRefChir()->loadRefFunction();
        $_operation->loadRefPatient();
        $_operation->loadRefPlageOp();
    }
}

$userSel       = CMediusers::get($chir_id);
$past_consults = [];
$today         = CMbDT::date();
foreach ($patient->loadRefsConsultations(null, 30) as $_consult) {
    $_consult->getType();
    $_consult->loadRefPlageConsult();
    $function = $_consult->loadRefPraticien()->loadRefFunction();

    if ($_consult->sejour_id) {
        unset($patient->_ref_consultations[$_consult->_id]);
        if (isset($patient->_ref_sejours[$_consult->sejour_id])) {
            $patient->_ref_sejours[$_consult->sejour_id]->_ref_consultations[$_consult->_id] = $_consult;
        }
    } else {
        if ($chir_id && $function->_id == $userSel->function_id && !$_consult->annule) {
            /* Récupération des consultations non soldées pour la fonction du praticien de la consultation */
            if ($_consult->_date < $today) {
                $_consult->loadRefFacture()->loadRefsReglements();
                if ($_consult->_ref_facture->_du_restant_patient > 0 && !$_consult->_ref_facture->patient_date_reglement) {
                    $past_consults[$_consult->_id] = $_consult;
                }
            }
        }
    }

    if (($function->group_id != $group->_id) && ($multi_group == "hidden")) {
        unset($patient->_ref_consultations[$_consult->_id]);
        continue;
    }
    $_consult->loadRefFacture()->loadRefsNotes();
}

//Evènements à rappeler du patient
$where      = [
    "rappel" => " = '1'",
    "date"   => " >= '" . CMbDT::format(null, "%Y-00-00") . "'",
];
$evenements = $patient->loadRefDossierMedical()->loadRefsEvenementsPatient($where);
foreach ($evenements as $_evt) {
    $_evt->loadRefPraticien()->loadRefFunction();
}

$consultation = new CConsultation();
$consultation->load($consultation_id);

CAccessMedicalData::logAccess($consultation);

$consultation->loadRefConsultAnesth();

if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($consultation, $consultation->loadRefGroup()->_id);
    $patient->loadRefStatusPatientUser();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("is_anesth", $is_anesth);
$smarty->assign("consultation", $consultation);
$smarty->assign("patient", $patient);
$smarty->assign("show_dhe_ecap", $show_dhe_ecap);
$smarty->assign("past_consults", $past_consults);
$smarty->assign('mode_cabinet', $mode_cabinet);
$smarty->display("httpreq_get_last_refs");
