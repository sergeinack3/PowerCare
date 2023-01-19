<?php
/**
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Use for external request and redirect to the right module/view
 */

CCanDo::checkRead();

// Call parameters, all parameters HAVE to be present in the tokenize view as well
$ipp                     = CView::get('ipp', 'str');
$nda                     = CView::get('nda', 'str');
$nom                     = CView::get('name', 'str');
$prenom                  = CView::get('firstname', 'str');
$date_naiss              = CView::get('birthdate', 'str');
$date_sejour             = CView::get('admit_date', 'str');
$group_tag               = CView::get('group_tag', 'str');
$group_idex              = CView::get('group_idex', 'str');
$sejour_tag              = CView::get('sejour_tag', 'str');
$sejour_idex             = CView::get('sejour_idex', 'str');
$view                    = CView::get('view', 'str default|none');
$show_menu               = CView::get('show_menu', 'bool default|0');
$retourURL               = CView::get('RetourURL', 'str');
$cabinet_id              = CView::get('cabinet_id', 'str');
$ext_patient_id          = CView::get('ext_patient_id', 'str');
$rpps                    = CView::get('rpps', 'str');
$context_guid            = CView::get('context_guid', 'str');
$consultation_id         = CView::get('consultation_id', 'ref class|CConsultation');
$consultation_patient_id = CView::get('patient_id', 'ref class|CPatient');
$rpps_praticien          = CView::get('rpps_praticien', 'str');
$numero_finess           = CView::get('numero_finess', 'str');
$tabs                    = CView::get('tabs', 'str');

$_GET['dialog'] = $show_menu ? '0' : '1';

CView::checkin();

$nb_patients = 0;

// View list
// PATTERN : MODULE , AJAX, TYPE
$mods_available = [
    'patient'                        => ['patients', 'ajax_vw_patient_complete', 'patient'], // dossier patient
    'full_patient'                   => ['patients', 'vw_full_patients', 'patient'], // dossier complet patient
    'soins'                          => ['soins', 'vw_dossier_sejour', 'sejour'],  // dossier de soins (complet)
    'prescription_pre_admission'     => ['soins', 'vw_dossier_sejour', 'sejour'],  // prescription de pré-admission
    'prescription'                   => ['soins', 'vw_dossier_sejour', 'sejour'],  // prescription de séjour
    'prescription_sortie'            => ['soins', 'vw_dossier_sejour', 'sejour'],  // prescription de sortie
    'constantes_medicales'           => ['patients', 'httpreq_vw_constantes_medicales', 'sejour'],  // surveillance
    'labo'                           => ['Imeds', 'httpreq_vw_sejour_results', 'sejour'],  // labo result
    'ecap_home'                      => ['ecap', 'home', 'none'],    // eCap - HOME
    'ecap_soins_prescription_sejour' => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_patient'             => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_constantes'          => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_suivi'               => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_prescription'        => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_labo'                => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_documents'           => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_soins_antecedents'         => ['soins', 'viewDossierSejour', 'sejour'],  // eCap - Soins
    'ecap_ssr'                       => ['ssr', 'vw_aed_sejour_ssr', 'sejour'],  // eCap - SSR
    'ecap_ssr_antecedents'           => ['ssr', 'vw_aed_sejour_ssr', 'sejour'],  // eCap - SSR
    'ecap_ssr_bilan'                 => ['ssr', 'vw_aed_sejour_ssr', 'sejour'],  // eCap - SSR
    'ecap_ssr_autonomie'             => ['ssr', 'vw_aed_sejour_ssr', 'sejour'],  // eCap - SSR
    'ecap_ssr_planification'         => ['ssr', 'vw_aed_sejour_ssr', 'sejour'],  // eCap - SSR
    'prestations'                    => ['planningOp', 'viewPrestations', 'sejour'],  // Prestations manage
    'intervention'                   => ['planningOp', 'vw_edit_planning', 'patient'],  // DHE
    'sejour'                         => ['planningOp', 'vw_edit_sejour', 'patient'],  // Création de séjour
    'documents'                      => ['patients', 'ajax_add_doc', 'patient'],  // Access documents
    'oxCabinet_timeline'             => ['oxCabinet', 'externalTimelineView', 'patient'],  // Timeline du patient
    'oxCabinet_appointment'          => ['oxCabinet', 'externalAppointmentView', 'consultation'],
    'oxCabinet_consultation'         => ['oxCabinet', 'externalConsultationView', 'consultation'],
    'module_dentaire'                => ['dentaire' , 'displayDashboard' , 'patient'],
];

//-----------------------------------------------------------------
// VIEWS

// view = none
if ($view == 'none') {
    CAppUI::stepAjax('context-view_required', UI_MSG_ERROR);
}

// View not registered
if (!array_key_exists($view, $mods_available)) {
    CAppUI::stepAjax('context-view_not-registered', UI_MSG_ERROR, $view);
}

// Check for module (with hack)
$this_module = $mods_available[$view][0];

if (!CModule::exists($this_module) && !CModule::exists("dP{$this_module}")) {
    CAppUI::stepAjax('context-module%s-not-activated', UI_MSG_ERROR, $this_module);
}

$g = CGroups::get()->_id;

// GROUP
if ($group_tag && $group_idex) {
    $tag = CIdSante400::getMatch('CGroups', $group_tag, $group_idex);

    if ($tag && $tag->_id) {
        $g = $tag->object_id;
    }
}

// PATIENT

// Find a patient
$patient = new CPatient();

// IPP Case
if ($ipp) {
    $patient->_IPP = $ipp;
    $patient->loadFromIPP($g);
    if ($patient->_id) {
        $nb_patients = 1;
    }
}

// Global case
if (!$nb_patients) {
    $patient->nom       = trim($nom);
    $patient->prenom    = trim($prenom);
    $patient->naissance = $date_naiss;
    $patient->loadMatchingPatient();
}

if (!$patient->_id && $ext_patient_id) {
    $idex = CIdSante400::getMatch("CPatient", "ext_patient_id-$cabinet_id", $ext_patient_id);
    if ($idex->_id) {
        $patient = $idex->loadTargetObject();
    }
}


//-----------------------------------------------------------------
// CONSULTATION
if ($mods_available[$view][2] == 'consultation') {

    $consultation = CConsultation::findOrNew($consultation_id);
    $patient      = CPatient::findOrNew($consultation_patient_id);

    if ($view === 'oxCabinet_consultation' && !$consultation->_id) {
        CAppUI::stepAjax('context-consultation-Invalid_consult_id', UI_MSG_ERROR, $view);
    }

    $url = formRequest($mods_available[$view]);
    if ($tabs) {
        $url .= "&tabs[]=" . implode("&tabs[]=", $tabs);
    }

    if ($consultation->_id) {
        $url .= "&appointment_id={$consultation->_id}";
    } elseif ($patient->_id && $rpps_praticien && $numero_finess) {
        $url .= "&patient_id={$patient->_id}&rpps_praticien={$rpps_praticien}&numero_finess={$numero_finess}";
    }

    CAppUI::redirect($url);
}

//-----------------------------------------------------------------
// SEJOUR

$sejour = new CSejour();

// Contexte séjour
if ($mods_available[$view][2] == 'sejour') {
    if ($nda) {
        $sejour->loadFromNDA($nda, $g);
    } elseif ($sejour_tag && $sejour_idex) {
        $tag = CIdSante400::getMatch($sejour->_class, $sejour_tag, $sejour_idex);

        if ($tag && $tag->_id) {
            $sejour = $tag->loadTargetObject();
        }
    } // Patient, with a date = sejour
    elseif ($patient->_id) {
        if (!$date_sejour) {
            CAppUI::stepAjax('context-sejour-patientOK-date-required', UI_MSG_ERROR, $view);
        }

        $date_sejour = CMbDT::dateTime($date_sejour);

        $where   = [
            'patient_id' => "= '$patient->_id'",
        ];
        $where[] = "'$date_sejour' BETWEEN entree AND sortie";

        $sejours = $sejour->countList($where);

        switch ($sejours) {
            case 0:
                CAppUI::stepAjax('context-none-sejour', UI_MSG_ERROR);
                break;

            case 1:
                $sejour->loadObject($where);
                break;

            default:
                CAppUI::stepAjax('context-multiple-sejour', UI_MSG_ERROR, $sejours);
        }
    } // Something is missing
    else {
        CAppUI::stepAjax('context-nda-or-PatientPlusDate-required', UI_MSG_ERROR, $view);
    }
}
// Contexte Patient
if ($mods_available[$view][2] == 'patient') {
    if (!$patient->_id && !in_array($view, ["intervention", "sejour", "documents"])) {
        if ($patient->_IPP) {
            CAppUI::stepAjax('context-nonexisting-patient-ipp%s', UI_MSG_ERROR, $patient->_IPP);
        } else {
            CAppUI::stepAjax('context-nonexisting-patient', UI_MSG_ERROR);
        }
    }

    $field_patient_id = ($view == "intervention") ? "pat_id" : "patient_id";
    $url              = formRequest($mods_available[$view]) . "&$field_patient_id={$patient->_id}&g={$g}";
    if ($view === "intervention" || $view === "sejour") {
        $url            .= "&contextual_call=1&cabinet_id=$cabinet_id&ext_patient_id=$ext_patient_id&ext_patient_nom=$nom&ext_patient_prenom=$prenom&ext_patient_naissance=$date_naiss";
        $type_id_object = $view === "intervention" ? "operation_id" : "sejour_id";
        $context_id     = $context_guid ? explode('-', $context_guid)[1] : 0;
        $url            .= "&$type_id_object=$context_id";
    } elseif ($view === "documents") {
        $url .= "&context_guid=$context_guid&cabinet_id=$cabinet_id";
    }
    CAppUI::redirect($url);
}

//-----------------------------------------------------------------
// labo
if ($mods_available[$view][2] == 'sejour') {
    if (!$sejour->_id) {
        CAppUI::stepAjax('context-none-sejour', UI_MSG_ERROR);
    }

    if ($mods_available[$view][1] == 'httpreq_vw_constantes_medicales') {
        // La vue Surveillance prends en paramètre le Guid et non l'id
        $url = formRequest($mods_available[$view]) . "&context_guid={$sejour->_guid}&g={$g}";
    } else {
        $url = formRequest($mods_available[$view]) . "&sejour_id={$sejour->_id}&g={$g}";
    }


    // Pré-selection de la prescription dans le dossier de soins
    switch ($view) {
        case 'prescription':
            $url .= '&default_tab=prescription_sejour';
            break;

        case 'prescription_sortie':
            $url .= '&default_tab=prescription_sejour&type_prescription=sortie';
            break;

        case 'prescription_pre_admission':
            $url .= '&default_tab=prescription_sejour&type_prescription=pre_admission';
            break;

        case 'ecap_soins_prescription_sejour':
            $url .= '&default_tab=prescription_sejour';
            break;

        case 'ecap_soins_patient':
            $url .= '&default_tab=patient';
            break;

        case 'ecap_soins_constantes':
            $url .= '&default_tab=constantes';
            break;

        case 'ecap_soins_suivi':
            $url .= '&default_tab=suivi_clinique';
            break;

        case 'ecap_soins_prescription':
            $url .= '&default_tab=prescription';
            break;

        case 'ecap_soins_labo':
            $url .= '&default_tab=labo';
            break;

        case 'ecap_soins_documents':
            $url .= '&default_tab=documents';
            break;

        case 'ecap_soins_antecedents':
            $url .= '&default_tab=antecedents';
            break;


        case 'ecap_ssr_antecedents':
            $url .= '#antecedents';
            break;

        case 'ecap_ssr_bilan':
            $url .= '#bilan';
            break;

        case 'ecap_ssr_autonomie':
            $url .= '#autonomie';
            break;

        case 'ecap_ssr_planification':
          $url .= '#planification';
          break;

        case 'prestations':
          $url .= '&with_buttons=&is_contextual_call=1';
          break;

        default:
    }

    CAppUI::redirect($url);
}

if ($mods_available[$view][2] == 'none') {
    $url = formRequest($mods_available[$view]) . "&g={$g}";

    CAppUI::redirect($url);
}

/**
 * Create the redirect string from an array
 *
 * @param array $requete array of ["module", "action", ...]
 *
 * @return string
 */
function formRequest($requete)
{
    $redirect = "m={$requete[0]}&a={$requete[1]}";

    if (CValue::get('dialog', 1)) {
        $redirect .= '&dialog=1';
    }

    return $redirect;
}
