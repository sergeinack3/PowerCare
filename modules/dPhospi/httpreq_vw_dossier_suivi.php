<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineComment;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\System\Forms\CExObject;

$user = CMediusers::get();

if (!$user->isPraticien()) {
  CCanDo::checkRead();
}

$group_guid = CGroups::loadCurrent()->_guid;

$check_show_const_transmission = CAppUI::pref("check_show_const_transmission");
$see_volet_diet                = CAppUI::gconf("soins Other see_volet_diet");
$check_show_diet               = CAppUI::pref("check_show_diet");

$sejour_id          = CView::get("sejour_id", "ref class|CSejour");
$user_id            = CView::get("user_id", "ref class|CMediusers");
$function_id        = CView::get("function_id", "ref class|CFunctions");
$cible              = CView::get("cible", "str", true);
$other_sejour_id    = CView::get("other_sejour_id", "str");
$_show_obs          = CView::get("_show_obs", "bool default|1", true);
$_degre_obs         = CView::get("_degre_obs", "str default|all", true);
$_type_obs          = CView::get("_type_obs", "str default|all", true);
$_etiquette_obs     = CView::get("_etiquette_obs", "str default|all", true);
$_show_trans        = CView::get("_show_trans", "bool default|1", true);
$_lvl_trans         = CView::get("_lvl_trans", "str default|all", true);
$_show_const        = CView::get("_show_const", "bool default|" . ($check_show_const_transmission ? 1 : 0), true);
$_show_diet         = CView::get("_show_diet", "bool default|" . ($check_show_diet ? 1 : 0), true);
$show_header        = CView::get("show_header", "bool default|0", true);
$show_cancelled     = CView::get("show_cancelled", "bool default|0", true);
$show_adm_cancelled = CView::get("show_adm_cancelled", "bool default|1", true);
$show_rdv_externe   = CView::get("show_rdv_externe", "bool default|0", true);
$show_call          = CView::get("show_call", "bool default|0", true);
$only_macrocible    = CView::get("only_macrocible", "bool", true);

CView::checkin();

if ($cible != "") {
  $_show_obs = $_show_const = 0;
}

$cible = stripslashes($cible);

$cibles = [
  "opened" => [],
  "closed" => []
];

$last_trans_cible = [];

$users = [];
$functions = [];

// Chargement du sejour
$sejour = CSejour::findOrFail($sejour_id);

CAccessMedicalData::logAccess($sejour);

// Chargements des séjours du patient
$sejour_context             = new CSejour();
$sejour_context->patient_id = $sejour->patient_id;
$sejour_context->annule     = 0;

$sejours_context = $sejour_context->loadMatchingList("entree ASC");

$current_group_id = CGroups::get()->_id;
/** @var CSejour $_sejour */
foreach ($sejours_context as $_sejour) {
  $sharing_files = CAppUI::gconf('dPpatients sharing multi_group', $_sejour->group_id);
  if ($sharing_files !== 'full' && $_sejour->group_id !== $current_group_id) {
    unset($sejours_context[$_sejour->_id]);
    continue;
  }
  $_sejour->loadRefPatient();
}

// Chargement du suivi médical suivant le contexte demandé
switch ($other_sejour_id) {
  case "all":
    $sejour->_ref_suivi_medical = [];
    foreach ($sejours_context as $_sejour_context) {
      $_sejour_context->loadSuiviMedical(null, $cible, $cibles, $last_trans_cible, $user_id, $users, $function_id, $functions);
      $sejour->_ref_suivi_medical = array_merge_recursive($sejour->_ref_suivi_medical, $_sejour_context->_ref_suivi_medical);
    }

    break;
  default:
    if ($other_sejour_id) {
      // Cas du changement de patient
      if (isset($sejours_context[$other_sejour_id])) {
        $sejour->load($other_sejour_id);

        CAccessMedicalData::logAccess($sejour);
      }
      else {
        $other_sejour_id = null;
      }
    }

    $sejour->loadSuiviMedical(null, $cible, $cibles, $last_trans_cible, $user_id, $users, $function_id, $functions);

    if ($other_sejour_id) {
      $sejour->load($sejour_id);
    }
}

$sejour->loadRefPraticien();

if ($show_header) {
  $sejour->loadRefPatient()->loadRefPhotoIdentite();
}

$sejour->loadRefPrescriptionSejour();
$prescription =& $sejour->_ref_prescription_sejour;

$is_praticien   = $user->isPraticien();
$has_obs_entree = 0;

//TODO: Revoir l'ajout des constantes dans le suivi de soins
//Ajout des constantes
if (!$cible && $_show_const) {
  $sejour->loadRefConstantes($user_id);
}

//mettre les transmissions dans un tableau dont l'index est le datetime
$list_trans_const = [];

$forms_active          = CModule::getActive("forms");
CExObject::$_load_lite = true;

$cancelled_nb = 0;

foreach ($sejour->_ref_suivi_medical as $_key => $_trans_const) {
  // Cas de l'affichage ligne par ligne
  if (($_trans_const instanceof CTransmissionMedicale || $_trans_const instanceof CObservationMedicale) && $_trans_const->cancellation_date) {
    $cancelled_nb++;
    if (!$show_cancelled) {
      continue;
    }
  }
  // Cas de l'affichage regroupé DAR
  if (is_array($_trans_const) && $_trans_const[0]->cancellation_date) {
    $cancelled_nb++;
    if (!$show_cancelled) {
      continue;
    }
  }

  if ($_trans_const instanceof CConsultation && !$_show_obs) {
    continue;
  }

  if ($_trans_const instanceof CObservationMedicale) {
    if ($see_volet_diet) {
      if (!$_show_diet && !$_show_obs) {
        continue;
      }
      if ($_show_diet && !$_show_obs && $_trans_const->etiquette !== 'dietetique') {
        continue;
      }
      if (!$_show_diet && $_show_obs && $_trans_const->etiquette === 'dietetique') {
        continue;
      }
    }
    elseif (!$_show_obs) {
      continue;
    }
  }

  if (is_array($_trans_const)) {
    if ($see_volet_diet) {
      if (!$_show_diet && !$_show_trans) {
        continue;
      }
      if ($_show_diet && !$_show_trans && !$_trans_const[0]->dietetique) {
        continue;
      }
      if (!$_show_diet && $_show_trans && $_trans_const[0]->dietetique) {
        continue;
      }
    }
    elseif (!$_show_trans && !$only_macrocible) {
      continue;
    }
    if ($_lvl_trans == "high" && $_trans_const[0]->degre != "high") {
      continue;
    }

    if (!$show_adm_cancelled
      && $_trans_const[0]->_ref_cible->object_class == "CAdministration" && !$_trans_const[0]->_ref_object->quantite
    ) {
      continue;
    }

    if ($only_macrocible
      && ($_trans_const[0]->_ref_cible->object_class !== "CCategoryPrescription" || !$_trans_const[0]->_ref_object->cible_importante)
    ) {
      continue;
    }
  }
  if ($_trans_const instanceof CConstantesMedicales) {
    $sort_key                    = "$_trans_const->datetime $_trans_const->_guid";
    $list_trans_const[$sort_key] = $_trans_const;
  }
  elseif ($_trans_const instanceof CConsultation) {
    // On n'affiche pas les consultations annulées
    if ($_trans_const->annule) {
      unset($sejour->_ref_suivi_medical[$_key]);
      continue;
    }

    foreach ($_trans_const->_refs_dossiers_anesth as $key => $_dossier_anesth) {
      $_dossier_anesth->loadRefOperation();
    }

    if ($_trans_const->type == "entree") {
      $has_obs_entree = 1;
    }

    if ($forms_active) {
      $forms = CExObject::loadExObjectsFor($_trans_const);

      foreach ($_trans_const->_refs_dossiers_anesth as $_dossier_anesth) {
        $_forms = CExObject::loadExObjectsFor($_dossier_anesth);
        $forms  += $_forms;
      }
      $_trans_const->_list_forms = $forms;
    }

    $list_trans_const["$_trans_const->_datetime $_trans_const->_guid"] = $_trans_const;
  }
  elseif ($_trans_const instanceof CPrescriptionLineElement || $_trans_const instanceof CPrescriptionLineComment) {
    $list_trans_const["$_trans_const->debut $_trans_const->time_debut"] = $_trans_const;
    $_trans_const->loadRefPraticien();
    continue;
  }
  elseif (is_array($_trans_const)) {
    $list_trans_const[$_key] = $_trans_const;
  }
  elseif ($_trans_const instanceof CObservationMedicale) {
    if ($_degre_obs !== "all" && $_trans_const->degre !== $_degre_obs) {
      continue;
    }
    // Pas de double == car le type peut être une chaîne vide
    elseif ($_type_obs !== "all" && $_trans_const->type != $_type_obs) {
      continue;
    }
    elseif ($_etiquette_obs !== "all" && $_trans_const->etiquette != $_etiquette_obs) {
      continue;
    }
    $sort_key                    = "$_trans_const->date $_trans_const->_guid";
    $list_trans_const[$sort_key] = $_trans_const;
  }
  else {
    $sort_key                    = "$_trans_const->debut $_trans_const->time_debut $_trans_const->_guid";
    $list_trans_const[$sort_key] = $_trans_const;
  }
}

// RDV externes
if ($show_rdv_externe) {
  $rdv_externes = $sejour->loadRefsRDVExternes(["rdv_externe.statut" => " != 'annule'"]);

  foreach ($rdv_externes as $_rdv) {
    $_rdv->loadRefSejour()->loadRefPatient();
    $list_trans_const["$_rdv->date_debut $_rdv->_guid"] = $_rdv;
  }
}

// Appel J-1 et J+1
if ($show_call) {
  $sejour->loadRefsAppel();

  foreach ($sejour->_ref_appels as $_appel) {
    $_appel->loadRefUser();
    $list_trans_const["$_appel->datetime $_appel->_guid"] = $_appel;
  }
}

ksort($cibles["opened"]);
ksort($cibles["closed"]);
krsort($list_trans_const);

$count_trans                = count($list_trans_const);
$sejour->_ref_suivi_medical = $list_trans_const;

$count_macrocibles = CCategoryPrescription::countMacrocibles();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("params", CConstantesMedicales::$list_constantes);
$smarty->assign("page_step", 20);
$smarty->assign("readOnly", CValue::get("readOnly", false));
$smarty->assign("count_trans", $count_trans);
$smarty->assign("user", $user);
$smarty->assign("isPraticien", $is_praticien);
$smarty->assign("isAnesth", $user->isAnesth());
$smarty->assign("sejour", $sejour);
$smarty->assign("prescription", $prescription);
$smarty->assign("cibles", $cibles);
$smarty->assign("cible", $cible);
$smarty->assign("users", $users);
$smarty->assign("user_id", $user_id);
$smarty->assign("functions", $functions);
$smarty->assign("function_id", $function_id);
$smarty->assign("has_obs_entree", $has_obs_entree);
$smarty->assign("last_trans_cible", $last_trans_cible);
$smarty->assign("_show_obs", $_show_obs);
$smarty->assign("_degre_obs", $_degre_obs);
$smarty->assign("_type_obs", $_type_obs);
$smarty->assign("observation_med", new CObservationMedicale());
$smarty->assign("_etiquette_obs", $_etiquette_obs);
$smarty->assign("_show_trans", $_show_trans);
$smarty->assign("_show_const", $_show_const);
$smarty->assign("_show_diet", $_show_diet);
$smarty->assign("_lvl_trans", $_lvl_trans);
$smarty->assign("show_header", $show_header);
$smarty->assign("cancelled_nb", $cancelled_nb);
$smarty->assign("show_cancelled", $show_cancelled);
$smarty->assign("count_macrocibles", $count_macrocibles);
$smarty->assign("show_adm_cancelled", $show_adm_cancelled);
$smarty->assign("only_macrocible", $only_macrocible);
$smarty->assign("sejours_context", $sejours_context);
$smarty->assign("other_sejour_id", $other_sejour_id);
$smarty->assign("show_rdv_externe", $show_rdv_externe);
$smarty->assign("show_call", $show_call);
$smarty->display("inc_vw_dossier_suivi");
