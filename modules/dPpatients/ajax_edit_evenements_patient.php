<?php

/**
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTypeEvenementPatient;

CCanDo::checkRead();
$patient_id           = CView::getRefCheckEdit("patient_id", "ref class|CPatient");
$show_list_evts       = CView::get("show_list_evts", "num default|0");
$view_mode            = CView::get("view_mode", "num default|0");
$evenement_patient_id = CView::get("evenement_patient_id", "num");
$inner_content        = CView::get("inner_content", "num default|0");
$prat_selected        = CView::get("praticien_id", "ref class|CMediusers", true);
$event_type_id        = CView::get("event_type", "ref class|CTypeEvenementPatient");
CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);
$patient->canDo();

$event_type = CTypeEvenementPatient::find($event_type_id);

$praticien     = new CMediusers();
$user          = CMediusers::get();
$register_date = true;
$is_sih_event  = false;

$praticiens = $praticien->loadPraticiens();
$praticiens += $praticien->loadListFromType(["Infirmière"]);

if ($prat_selected) {
    $praticien->load($prat_selected);
} elseif ($user->isPraticien()) {
    $praticien = $user;
} elseif (count($praticiens) == 1) {
    $praticien = reset($praticiens);
}

if ($show_list_evts) {
    $dossier_medical = null;

    if ($patient->_id) {
        $dossier_medical = $patient->loadRefDossierMedical();
        $dossier_medical->loadRefObject();
        $dossier_medical->loadRefsEvenementsPatient([], true);
        foreach ($dossier_medical->_ref_evenements_patient as $_evenement) {
            $_evenement->loadRefTypeEvenementPatient();
            $_evenement->loadRefPraticien()->loadRefFunction();
            $_evenement->countDocItems();
            $_evenement->loadRefNotification();
            $_evenement->loadRefsUsers(false);
            $_evenement->loadRefsCodesLoinc();
            $_evenement->loadRefsCodesSnomed();
            $_evenement->loadRefsId400SIH();
        }
    }
} else {
    $types = (new CTypeEvenementPatient())->loadListWithPerms(PERM_EDIT);
}

$evenement_patient = new CEvenementPatient();

if ($evenement_patient_id) {
    $evenement_patient->load($evenement_patient_id);
    $evenement_patient->loadRefPraticien();
    $evenement_patient->countActes();
    $evenement_patient->loadRefsUsers();
    $evenement_patient->loadRefsCodesLoinc();
    $evenement_patient->loadRefsCodesSnomed();
    $evenement_patient->loadRefsId400SIH();
    if ($evenement_patient->_ref_cabinet_id400) {
        $register_date = false;
        $is_sih_event = true;
    }
} else {
    $evenement_patient->praticien_id = $praticien->_id;
}

$smarty = new CSmartyDP();
$smarty->assign("inner_content", $inner_content);
$smarty->assign("patient", $patient);
$smarty->assign("mailing", $event_type ?? false);

if (!$show_list_evts) {
    $smarty->assign("evenement_patient", $evenement_patient);
    $smarty->assign("register_date", $register_date);
    $smarty->assign("types", $types);
    $smarty->assign("praticien", $praticien);
    $smarty->assign("praticiens", $praticiens);
    $smarty->assign("is_sih_event", $is_sih_event);
    $smarty->display("inc_edit_evenement_patient");
} else {
    $smarty->assign("dossier_medical", $dossier_medical);
    $smarty->assign("view_mode", $view_mode);
    $smarty->display("inc_vw_evenements_patient");
}
