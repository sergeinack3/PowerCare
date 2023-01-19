<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $m;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

// Permissions ?
//$module = CModule::getInstalled($m);
//$canModule = $module->canDo();
//$canModule->needsEdit();

$prat_id                       = CValue::post("_prat_id");
$patient_id                    = CValue::post("patient_id");
$_operation_id                 = CValue::post("_operation_id");
$_datetime                     = CValue::post("_datetime");
$callback                      = CValue::post("callback");
$type                          = CValue::post("type");
$_in_suivi                     = CValue::post("_in_suivi", 0);
$_in_maternite                 = CValue::post("_in_maternite", 0);
$grossesse_id                  = CValue::post("grossesse_id");
$_sejour_id                    = CValue::post("_sejour_id");
$uf_soins_id                   = CValue::post("_uf_soins_id");
$uf_medicale_id                = CValue::post("_uf_medicale_id");
$charge_id                     = CValue::post("_charge_id");
$unique_lit_id                 = CValue::post("_unique_lit_id");
$lit_id                        = CValue::post("lit_id");
$service_id                    = CValue::post("service_id");
$mode_entree                   = CValue::post("mode_entree");
$mode_entree_id                = CValue::post("mode_entree_id");
$teleconsultation              = CValue::post('teleconsultation');
$type_suivi                    = CValue::post("type_suivi");
$_create_sejour_activite_mixte = CValue::post("_create_sejour_activite_mixte");
$tab_mode                      = CValue::post("tab_mode", "tab");
$_force_create_sejour          = CValue::post('_force_create_sejour');
$ccmu                          = CView::post("ccmu", "enum list|" . implode("|", CConsultation::CCMU_VALUES));
$cimu                          = CView::post("cimu", "enum list|" . implode("|", CConsultation::CIMU_VALUES));
$function_id                   = CView::post('_function_id', "ref class|CFunctions");
$type_consultation             = CView::post("type_consultation", "str default|consultation");

// External entity fields
$date_creation_anterieure = CValue::post('date_creation_anterieure');
$agent                    = CValue::post('agent');

if (!$_datetime || $_datetime == "now") {
    $_datetime = CMbDT::dateTime();
}

$sejour = new CSejour();
$sejour->load($_sejour_id ?: CValue::post("sejour_id"));

// Cas des urgences
if (in_array($sejour->type, CSejour::getTypesSejoursUrgence($sejour->praticien_id)) && !$_in_suivi) {
    if ($_datetime < $sejour->entree || $_datetime > $sejour->sortie) {
        CAppUI::setMsg("La prise en charge doit être dans les bornes du séjour", UI_MSG_ERROR);
        if ($ajax) {
            echo CAppUI::getMsg();
            CApp::rip();
        }
        CAppUI::redirect("m=dPurgences");
    }

    $sejour->loadRefsConsultations();
    if ($sejour->_ref_consult_atu->_id) {
        CAppUI::setMsg("Patient déjà pris en charge par un praticien", UI_MSG_ERROR);
        if ($ajax) {
            echo CAppUI::getMsg();
            CApp::rip();
        }
        CAppUI::redirect("m=dPurgences");
    }

    // Changement de praticien pour le sejour
    if (CAppUI::conf("dPurgences pec_change_prat")) {
        $sejour->praticien_id = $prat_id;
        if ($msg = $sejour->store()) {
            CAppUI::setMsg($msg, UI_MSG_ERROR);
            if ($ajax) {
                echo CAppUI::getMsg();
                CApp::rip();
            }
            CAppUI::redirect("m=dPurgences");
        }

        if (CAppUI::gconf("dPurgences CRPU prat_affectation")) {
            $curr_aff = $sejour->loadRefCurrAffectation();
            if ($curr_aff->_id) {
                $curr_aff->praticien_id = $prat_id;
                if ($msg = $curr_aff->store()) {
                    CAppUI::setMsg($msg, UI_MSG_ERROR);
                    if ($ajax) {
                        echo CAppUI::getMsg();
                        CApp::rip();
                    }
                    CAppUI::redirect("m=dPurgences");
                }
            }
        }
    }
}

$chir = new CMediusers;
$chir->load($prat_id);
if (!$chir->_id) {
    CAppUI::setMsg("Vous devez choisir un praticien pour la consultation", UI_MSG_ERROR);
}

$day_now   = CMbDT::format($_datetime, "%Y-%m-%d");
$hour_now  = CMbDT::format($_datetime, "%H:00:00");
$hour_next = CMbDT::time("+1 HOUR", $hour_now);

$slots = CAppUI::gconf("dPcabinet ConsultationImmediate slots");

if ($slots != 1) {
    $time_now = CMbDT::timeGetNearestMinsWithInterval(CMbDT::dateTime($_datetime), $slots);
} else {
    $time_now = CMbDT::format($_datetime, "%H:%M:00");
}
$plage       = new CPlageconsult();
$plageBefore = new CPlageconsult();
$plageAfter  = new CPlageconsult();

// Cas ou une plage correspond
$where                        = [];
$where["chir_id"]             = "= '$chir->_id'";
$where["date"]                = "= '$day_now'";
$where["debut"]               = "<= '$time_now'";
$where["fin"]                 = "> '$time_now'";
$where["agenda_praticien_id"] = "IS NULL";
$plage->loadObject($where);

if (!$plage->_id) {
    // Cas ou on a des plage en collision
    $where                        = [];
    $where["chir_id"]             = "= '$chir->_id'";
    $where["date"]                = "= '$day_now'";
    $where["debut"]               = "<= '$hour_now'";
    $where["fin"]                 = ">= '$hour_now'";
    $where["agenda_praticien_id"] = "IS NULL";
    $plageBefore->loadObject($where);
    $where["debut"] = "<= '$hour_next'";
    $where["fin"]   = ">= '$time_now'";
    $plageAfter->loadObject($where);
    if ($plageBefore->_id) {
        if ($plageAfter->_id) {
            $plageBefore->fin = $plageAfter->debut;
        } else {
            $plageBefore->fin = max($plageBefore->fin, $hour_next);
        }
        $plage =& $plageBefore;
    } elseif ($plageAfter->_id) {
        $plageAfter->debut = min($plageAfter->debut, $hour_now);
        $plage             =& $plageAfter;
    } else {
        $plage->chir_id          = $chir->_id;
        $plage->date             = $day_now;
        $plage->freq             = "00:" . CPlageconsult::$minutes_interval . ":00";
        $plage->debut            = $hour_now;
        $plage->fin              = $hour_next;
        if ($type_consultation == "suivi_patient") {
            $plage->libelle          = CPlageconsult::LIBELLE_PLAGE_SUIVI_PATIENT;
        } else {
            $plage->libelle          = "automatique";
        }
        $plage->_immediate_plage = 1;
        $plage->function_id      = $function_id;
    }
    $plage->updateFormFields();
    if ($msg = $plage->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
}

if (CModule::getActive("oxCabinet")) {
    $plage->function_id = $function_id;
    if ($msg = $plage->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
}

$plage->loadRefsFwd();

if ($grossesse_id) {
    $grossesse = new CGrossesse();
    $grossesse->load($grossesse_id);
    if ($grossesse->_id) {
        $patient_id = $grossesse->parturiente_id;
    }
}

$ref_chir = $plage->_ref_chir;

$consult                                = new CConsultation;
$consult->plageconsult_id               = $plage->_id;
$consult->sejour_id                     = $sejour->_id;
$consult->grossesse_id                  = $grossesse_id;
$consult->patient_id                    = $patient_id;
$consult->heure                         = $time_now;
$consult->arrivee                       = "$day_now $time_now";
$consult->duree                         = 1;
$consult->chrono                        = CConsultation::PATIENT_ARRIVE;
$consult->date_at                       = CValue::post("date_at");
$consult->csnp                          = CModule::getActive('oxCabinet') ? CAppUI::gconf("oxCabinet CSNP is_csnp") : '';
$consult->ccmu                          = $ccmu;
$consult->cimu                          = $cimu;
$consult->lit_id                        = $lit_id;
$consult->_operation_id                 = $_operation_id;
$consult->_uf_soins_id                  = $uf_soins_id;
$consult->_uf_medicale_id               = $uf_medicale_id;
$consult->_charge_id                    = $charge_id;
$consult->_unique_lit_id                = $unique_lit_id;
$consult->_service_id                   = $service_id;
$consult->_type_suivi                   = $type_suivi;
$consult->_mode_entree                  = $mode_entree;
$consult->_mode_entree_id               = $mode_entree_id;
$consult->teleconsultation              = $teleconsultation;
$consult->type_consultation             = $type_consultation;
$consult->_create_sejour_activite_mixte = $_create_sejour_activite_mixte;
$consult->_in_maternite                 = $_in_maternite;
$consult->_force_create_sejour          = $_force_create_sejour;

// External entity fields
$consult->date_creation_anterieure = $date_creation_anterieure;
$consult->agent                    = $agent;

if ($type) {
    $consult->type = $type;
}

// Cas standard
if ($type_consultation == "consultation") {
    $consult->motif = CValue::post(
        "motif",
        CAppUI::tr(CAppUI::gconf('dPcabinet CConsultation default_message_immediate_consult'))
    );
}
if ($type == "entree") {
    $consult->motif = CAppUI::gconf('soins Other default_motif_observation');
}
// Cas des urgences
if (in_array($sejour->type, CSejour::getTypesSejoursUrgence($sejour->praticien_id))) {
    // Motif de la consultation
    $consult->motif = "";
    if (CAppUI::gconf('dPurgences CRPU motif_rpu_view')) {
        $consult->motif .= "RPU: ";

        $sejour->loadRefRPU();
        $consult->motif .= $sejour->_ref_rpu->diag_infirmier;
    }
}
if ($msg = $consult->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
} else {
    CAppUI::setMsg("CConsultation-msg-create", UI_MSG_OK);
}


// Redirect final
if ($ajax) {
    echo CAppUI::getMsg();
    if ($callback && $consult->_id) {
        CAppUI::callbackAjax($callback, $consult->_id, $consult->getProperties());
    }
    CApp::rip();
}

if ($current_m = CValue::post("_m_redirect")) {
    CAppUI::redirect("m=$current_m");
} else {
    $current_m = (in_array(
        $sejour->type,
        CSejour::getTypesSejoursUrgence($sejour->praticien_id)
    )) ? "urgences" : "cabinet";
    CAppUI::redirect("m=$current_m&$tab_mode=edit_consultation&selConsult=$consult->_id&chirSel=$chir->_id");
}
