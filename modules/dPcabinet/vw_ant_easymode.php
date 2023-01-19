<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;

CCanDo::check();

// @todo à transférer dans  dPpatient
// En l'état on ne peut pas vérifier les droits sur dPcabinet
// CCanDo::checkRead();

$patient_id = CView::getRefCheckEdit("patient_id", "ref class|CPatient");
$consult_id = CView::getRefCheckEdit('consult_id', 'ref class|CConsultation');
CView::checkin();

// On charge le praticien
$user = CMediusers::get();
$user->loadRefs();
$canUser = $user->canDo();

$consult = new CConsultation();
if ($consult_id) {
    $consult->load($consult_id);

    CAccessMedicalData::logAccess($consult);

    $consult->loadRefsFwd();
}

// Chargement des aides à la saisie
$antecedent = new CAntecedent();
$antecedent->loadAides($user->_id);

$aides_antecedent = $antecedent->_aides_all_depends["rques"] ? $antecedent->_aides_all_depends["rques"] : [];

// On charge le patient pour connaitre ses antécedents et traitements actuels
$patient = new CPatient();
$patient->load($patient_id);
$patient->loadRefDossierMedical();

$dossier_medical = $patient->_ref_dossier_medical;
$dossier_medical->loadRefsAntecedents();
$dossier_medical->loadRefsTraitements();

$applied_antecedents = [];

foreach ($dossier_medical->_ref_antecedents_by_type as $list) {
    foreach ($list as $a) {
        if (!isset($applied_antecedents[$a->type])) {
            $applied_antecedents[$a->type] = [];
        }

        $applied_antecedents[$a->type][] = $a->rques;
    }
}

$order_mode_grille = CAppUI::pref("order_mode_grille");

$fill_pref = $order_mode_grille != "";

$order_decode = [];

if ($fill_pref) {
    $order_decode = get_object_vars(json_decode($order_mode_grille));

    /** Suppression des types d'antécédents non cochés dans la configuration */
    foreach ($order_decode as $key => $_order_decode) {
        if (!in_array($key, $antecedent->_specs["type"]->_list)) {
            unset($order_decode[$key]);
        }
    }

    $keys = array_keys($order_decode);

    foreach ($keys as $_key => $_value) {
        if ($_value == "_empty_") {
            $keys[$_key] = "";
        }
    }
    $keys = array_flip($keys);

    $antecedent->_count_rques_aides = array_replace_recursive($keys, $antecedent->_count_rques_aides);
}

foreach ($aides_antecedent as $_depend_1 => $_aides_by_depend_1) {
    if ($fill_pref) {
        $key = $_depend_1 == "" ? "_empty_" : $_depend_1;
        if (isset($order_decode[$key])) {
            $keys                         = array_intersect(
                explode("|", $order_decode[$key]),
                array_keys($_aides_by_depend_1)
            );
            $keys                         = array_flip($keys);
            $aides_antecedent[$_depend_1] = array_replace_recursive($keys, $_aides_by_depend_1);
            $_aides_by_depend_1           = $aides_antecedent[$_depend_1];
        }
    }
    foreach ($_aides_by_depend_1 as $_depend_2 => $_aides_by_depend_2) {
        if (!is_array($_aides_by_depend_2)) {
            continue;
        }

        foreach ($_aides_by_depend_2 as $_aide) {
            if (isset($applied_antecedents[$_depend_1])) {
                foreach ($applied_antecedents[$_depend_1] as $_atcd) {
                    if ($_atcd == $_aide->text || strpos($_atcd, $_aide->text) === 0) {
                        $_aide->_applied = true;
                    }
                }
            }
        }
    }
}

foreach ($aides_antecedent as $type => $_aides_by_type) {
    foreach ($_aides_by_type as $appareil => $_aides_by_appareil) {
        $i          = 0;
        $temp_count = 0;
        $count      = round(count($_aides_by_appareil) / 4);
        $aides      = [];
        foreach ($_aides_by_appareil as $_aide) {
            $aides[$i][] = $_aide;
            $temp_count++;
            if ($temp_count > $count) {
                $temp_count = 0;
                $i++;
            }
        }
        $antecedent->_count_rques_aides_appareil[$type][$appareil] = count($_aides_by_appareil);
        $aides                                                     = CMbArray::transpose($aides);
        $aides_antecedent[$type][$appareil]                        = $aides;
    }
}

$aides_autocomplete = [];
foreach ($aides_antecedent as $_type => $aides_by_type) {
    foreach ($aides_by_type as $_appareil => $_aides_by_appareil) {
        foreach ($_aides_by_appareil as $_aides_by_line) {
            foreach ($_aides_by_line as $_aide) {
                $aides_autocomplete[$_aide->name] = [
                    "text"     => $_aide->text,
                    "type"     => $_type,
                    "appareil" => $_appareil,
                ];
            }
        }
    }
}

ksort($aides_autocomplete);

$applied_traitements = [];
foreach ($dossier_medical->_ref_traitements as $a) {
    $applied_traitements[$a->traitement] = true;
}

$traitement = new CTraitement();
$traitement->loadAides($user->_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("aides_antecedent", $aides_antecedent);
$smarty->assign("antecedent", $antecedent);
$smarty->assign("traitement", $traitement);
$smarty->assign("applied_antecedents", $applied_antecedents);
$smarty->assign("applied_traitements", $applied_traitements);
$smarty->assign("patient", $patient);
$smarty->assign("consult", $consult);
$smarty->assign("user_id", $user->_id);
$smarty->assign("order_mode_grille", $order_mode_grille);
$smarty->assign("aides_autocomplete", $aides_autocomplete);
$smarty->display("vw_ant_easymode");
