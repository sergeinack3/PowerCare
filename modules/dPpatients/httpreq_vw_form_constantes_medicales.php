<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

$constantes = new CConstantesMedicales();
$perms      = $constantes->canDo();
if (!$perms->read) {
  $perms->denied();
}

$const_id      = CValue::get('const_id', 0);
$context_guid  = CValue::get('context_guid');
$patient_id    = CValue::get('patient_id');
$can_edit      = CValue::get('can_edit');
$selection     = CValue::get('selection');
$host_guid     = CValue::get('host_guid');
$display_graph = CValue::get('display_graph', 1);
$unique_id     = CValue::get('unique_id', '');

$context = null;
if ($context_guid) {
  $context = CMbObject::loadFromGuid($context_guid);
}

/** @var CGroups|CService|CRPU $host */
// On cherche le meilleur "herbegement" des constantes, pour charger les configuration adequat
if ($host_guid) {
  $host = CMbObject::loadFromGuid($host_guid);
}
else {
  $host = CConstantesMedicales::guessHost($context);
}

$show_cat_tabs                       = CConstantesMedicales::getHostConfig("show_cat_tabs", $host);
$activate_choice_blood_glucose_units = CConstantesMedicales::getHostConfig(
    "activate_choice_blood_glucose_units",
    $host
);

$dates = array();
if (!$selection) {
  $selection = CConstantesMedicales::getConstantsByRank('form', true, $host);
}
else {
  $selection = CConstantesMedicales::selectConstants($selection, 'form');
}

foreach (CConstantesMedicales::$list_constantes as $key => $cst) {
  $dates["$key"] = CMbDT::format(null, CAppUI::conf("date"));
}

$patient_id = $constantes->patient_id ? $constantes->patient_id : $patient_id;
$patient    = CPatient::loadFromGuid("CPatient-$patient_id");
$patient->loadRefLatestConstantes(null, array("poids", "taille"), null, false);

$constantes = new CConstantesMedicales();
$constantes->load($const_id);
$constantes->loadRefContext();
$constantes->loadRefPatient()->evalAgeMois();
$constantes->updateFormFields(); // Pour forcer le chargement des unités lors de la saisie d'une nouvelle constante
$constantes->loadRefsComments();

if ($context) {
  $constantes->patient_id    = $patient_id;
  $constantes->context_class = $context->_class;
  $constantes->context_id    = $context->_id;
}

$modif_timeout = intval(CAppUI::conf("dPpatients CConstantesMedicales constants_modif_timeout", $host->_guid));
$can_create    = $perms->edit;
[$can_edit, $disable_edit_motif, $modif_timeout] = $constantes->getEditReleve(
    $perms,
    $constantes,
    $modif_timeout,
    $context_guid,
    $can_edit
);

$is_redon = 0;

if (count($constantes->loadRefReleveRedons()) > 0) {
    $can_edit = 0;
    $is_redon = 1;
}

$latest_constantes = CConstantesMedicales::getLatestFor($patient_id, $constantes->datetime, array(), $context, false);

// Calcul du Bilan Hydrique
$bh_type   = CConstantesMedicales::$list_constantes["_bilan_hydrique"]["type"];
$is_hidden = isset($selection["all"]["hidden"]) && in_array("_bilan_hydrique", $selection["all"]["hidden"]) ||
  isset($selection[$bh_type]["hidden"]) && in_array("_bilan_hydrique", $selection[$bh_type]["hidden"]) ||
  !count(CMbArray::searchRecursive('_bilan_hydrique', $selection));

if ($context && $context instanceof CSejour && !$is_hidden) {
  /** @var CSejour $context */;
  $bh_cst = CConstantesMedicales::getValeursHydriques($context);

  $presc_sejour = $context->loadRefPrescriptionSejour();

  if ($presc_sejour && $presc_sejour->_id) {
    $bh_perfs = $presc_sejour->calculEntreesHydriques();

    $bilan = CConstantesMedicales::calculBilanHydrique($bh_cst, $bh_perfs);

    foreach ($bilan as $_datetime => $_bilan) {
      /* Ajout du bilan hydrique dans les derniers valeurs des constantes */
      if ($_datetime <= $constantes->datetime) {
        $latest_values                           = $latest_constantes[0];
        $latest_values->_bilan_hydrique          = $_bilan['cumul'];
        $latest_constantes[1]['_bilan_hydrique'] = $_datetime;
      }
    }
  }
}

// Convert sugarblood values and units
$constantes                           = CConstantesMedicales::getConvertUnitGlycemie($constantes, false, $activate_choice_blood_glucose_units);
$latest_constantes[0]                 = CConstantesMedicales::getConvertUnitGlycemie($latest_constantes[0], false, $activate_choice_blood_glucose_units);
$constantes->unite_glycemie           = $constantes->_unite_glycemie;
$latest_constantes[0]->unite_glycemie = $constantes->unite_glycemie;

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('constantes', $constantes);
$smarty->assign('latest_constantes', $latest_constantes);
$smarty->assign('context_guid', $context_guid);
$smarty->assign('selection', $selection);
$smarty->assign('dates', $dates);
$smarty->assign('can_create', $can_create);
$smarty->assign('can_edit', $can_edit);
$smarty->assign('modif_timeout', $modif_timeout);
$smarty->assign('display_graph', $display_graph);
$smarty->assign('unique_id', $unique_id);
$smarty->assign('show_cat_tabs', $show_cat_tabs);
$smarty->assign('disable_edit_motif', $disable_edit_motif);
$smarty->assign('activate_choice_blood_glucose_units', $activate_choice_blood_glucose_units);
$smarty->assign('is_redon', $is_redon);

$smarty->display('inc_form_edit_constantes_medicales');
