<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Moebius\CMoebiusAPI;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CConstantGraph;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Services\ConstantesService;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;

/**
 * Convert a string in a float value
 *
 * @param string|null $v The value
 *
 * @return float|null
 */
function getValue($v) {
  return ($v === null) ? null : floatval($v);
}

$constantes = new CConstantesMedicales();
$perms      = $constantes->canDo();
if (!$perms->read) {
  $perms->denied();
}

$context_guid          = CView::get('context_guid', 'str');
$selected_context_guid = CView::get('selected_context_guid', 'str default|' . $context_guid);
$patient_id            = CView::get('patient_id', 'ref class|CPatient');
$can_edit              = CView::get('can_edit', 'bool');
$can_select_context    = CView::get('can_select_context', 'bool default|1');
$selection             = CView::get('selection', 'str');
$date_min              = CView::get('date_min', 'dateTime');
$date_max              = CView::get('date_max', 'dateTime');
$print                 = CView::get('print', 'bool default|0');
$paginate              = CView::get('paginate', 'bool default|0');
$start                 = CView::get('start', 'num default|0');
$count                 = CView::get('count', 'num default|50');
$view                  = CView::get('view', 'enum list|full|simple|pmsi default|full');
$host_guid             = CView::get('host_guid', 'str');
$infos_patient         = CView::get('infos_patient', 'bool default|0');
$mode_pdf              = CView::get("mode_pdf", 'bool default|0');
$hidden_graphs         = CView::get('hidden_graphs', 'str');
/** @var string $unique_id An UID for assuring the uniqueness of the function refreshConstantesMedicales,
 * if there is a need of displaying several constants views in the same page (like it can be case with MbForms) */
$unique_id = CView::get('unique_id', 'str');
$iframe    = CView::get('iframe', 'bool default|0');

CView::checkin();

if (is_null($can_edit) || $can_edit == '') {
  if ($context_guid != $selected_context_guid) {
    $can_edit = 0;
  }
  else {
    $can_edit = $perms->edit;
  }
}

if (!$start) {
  $start = 0;
}

if ($paginate) {
  $limit = "$start,$count";
}
else {
  $limit = $count;
}

$current_context = null;
if ($context_guid) {
  $current_context = CMbObject::loadFromGuid($context_guid);
}

if ($current_context && in_array($current_context->_class, ["CSejour", "CConsultation"])) {
  CAccessMedicalData::logAccess($current_context);
}

if ($current_context && $current_context->_class == "CSejour") {
  // Si le patient est en permission, on interdit la saisie
  $can_edit = !$current_context->isInPermission();
}

$custom_selection = $selection ? $selection : array();

/** @var CGroups|CService|CRPU $host */

// On cherche le meilleur "hebergement" des constantes, pour charger les configurations adequat
if ($host_guid) {
  $host = CMbObject::loadFromGuid($host_guid);
}
else {
  $host = CConstantesMedicales::guessHost($current_context);
}

$show_cat_tabs                       = CConstantesMedicales::getHostConfig("show_cat_tabs", $host);
$activate_choice_blood_glucose_units = CConstantesMedicales::getHostConfig(
    "activate_choice_blood_glucose_units",
    $host
);

if (!$selection || $selected_context_guid === 'all') {
  $selection = CConstantesMedicales::getConstantsByRank('form', $show_cat_tabs, $host);
}
else {
  $selection = CConstantesMedicales::selectConstants($selection, 'form', $host);
}

// If print mode, no need to include hidden graphs
if ($print) {
  $selection["all"]["hidden"] = array();
}

$old_constants_to_draw = ($print == 1 ? $selection : CConstantesMedicales::$list_constantes);


$constants_to_draw = $selection;

/** @var CMbObject|CPatient|CSejour $context */
if ($selected_context_guid !== 'all') {
  $context = CMbObject::loadFromGuid($selected_context_guid);
}
else {
  $context = CMbObject::loadFromGuid($context_guid);
}

if ($context->_class == "CConsultation" && $selected_context_guid !== 'all' && CModule::getActive("moebius")
  && CAppUI::pref('ViewConsultMoebius') && count($context->loadRefsDossiersAnesth())) {
  $selection = CMoebiusAPI::loadSelectionConstantes();
}

if ($context) {
  $context->loadRefs();

  if ($context instanceof CPatient) {
    $patient = $context;
  }
  else {
    $patient = $context->_ref_patient;
  }
}

if ($patient_id) {
  $patient = new CPatient;
  $patient->load($patient_id);
}

if ($selected_context_guid == "all") {
  $context = null;
}

// Blood sugar reports
$blood_sugar_reports = [];

$patient->loadRefPhotoIdentite();

$where = array(
  "patient_id" => " = '$patient->_id'"
);

// Construction d'une constante médicale
$constantes->patient_id = $patient->_id;
$constantes->loadRefPatient()->evalAgeMois();

// Les constantes qui correspondent (dans le contexte ou non)
$where_context                  = $where;
$where_context["context_class"] = "IS NOT NULL";
$where_context["context_id"]    = "IS NOT NULL";

$query = new CRequest;
$query->addTable($constantes->_spec->table);
$query->addColumn("context_class");
$query->addColumn("context_id");
$query->addWhere($where_context);
$query->addGroup(array("context_class", "context_id"));

$query         = $query->makeSelect();
$list          = $constantes->_spec->ds->loadList($query);
$list_contexts = array();

$multi_group = CAppUI::gconf("dPpatients sharing multi_group");

foreach ($list as $_context) {
  /** @var CMbObject $c */
  $c = new $_context["context_class"];
  $c = $c->getCached($_context["context_id"]);

  if ($c instanceof CSejour && $c->group_id !== CGroups::get()->_id  && $multi_group !== "full") {
    continue;
  }

  // Cas d'un RPU
  if ($c instanceof CConsultation && $c->sejour_id) {
    $c->loadRefSejour();
    if (in_array($c->_ref_sejour->type, CSejour::getTypesSejoursUrgence($c->_ref_sejour->praticien_id))) {
      continue;
    }
  }

  $c->loadRefsFwd();
  $list_contexts[$c->_guid] = $c;
}

if ($current_context instanceof CConsultation) {
  $current_context->loadComplete();
}

// Cas d'un RPU
if ($current_context instanceof CConsultation && $current_context->sejour_id) {
  $current_context->loadRefSejour();
  $current_context->_ref_sejour->loadRefRPU();
  if ($current_context->_ref_sejour->_ref_rpu && $current_context->_ref_sejour->_ref_rpu->_id) {
    $current_context = $current_context->_ref_sejour;
    $current_context->loadComplete();
    $context      = $current_context;
    $context_guid = $current_context->_guid;
  }
}
if (!isset($list_contexts[$current_context->_guid])) {
  $current_context->loadRefsFwd();
  $list_contexts[$current_context->_guid] = $current_context;
}

if (!count($list_contexts)) {
  $list_contexts[] = $current_context;
}

if ($context && $selected_context_guid !== 'all') {
  if ($context->_class == 'CSejour') {
    $context->loadRefsConsultations();
    $context->loadRefsConsultAnesth();
    if (!empty($context->_ref_consultations) || $context->_ref_consult_anesth) {
      $whereOr   = array();
      $whereOr[] = "(context_class = '$context->_class' AND context_id = '$context->_id')";
      foreach ($context->_ref_consultations as $_ref_consult) {
        $whereOr[] = "(context_class = '$_ref_consult->_class' AND context_id = '$_ref_consult->_id')";
      }
      if ($context->_ref_consult_anesth) {
        $consult   = $context->_ref_consult_anesth->loadRefConsultation();
        $whereOr[] = "(context_class = '$consult->_class' AND context_id = '$consult->_id')";
      }
      $where[] = implode(" OR ", $whereOr);
    }
    else {
      $where["context_class"] = " = '$context->_class'";
      $where["context_id"]    = " = '$context->_id'";
    }
  }
  else {
    $where["context_class"] = " = '$context->_class'";
    $where["context_id"]    = " = '$context->_id'";
  }

  // Needed to know if we are in the right context
  $constantes->context_class = $context->_class;
  $constantes->context_id    = $context->_id;
  $constantes->loadRefContext();
}

$latest_constantes = $patient->loadRefLatestConstantes(CMbDT::dateTime(), array(), $context, false);
// On récupère dans tous les cas le poids et la taille du patient
$patient->loadRefLatestConstantes(null, array("poids", "taille", "clair_creatinine"), null, false);

$constantes->updateFormFields(); // Pour forcer le chargement des unités lors de la saisie d'une nouvelle constante
$constantes->loadRefsComments();

$whereOr = array();
foreach ($constants_to_draw as $_cat => $_ranks) {
  foreach ($_ranks as $rank => $_constants) {
    foreach ($_constants as $name) {
      if ($name[0] === "_") {
        /* Adds the constants used for the calculation of computed constants */
        if (array_key_exists($name, CConstantesMedicales::$list_constantes)
            && array_key_exists('bases', CConstantesMedicales::$list_constantes[$name])
        ) {
          foreach (CConstantesMedicales::$list_constantes[$name]['bases'] as $base_constant) {
            $whereOr[] = "$base_constant IS NOT NULL ";
          }
        }

        continue;
      }
      $whereOr[] = "$name IS NOT NULL ";
    }
  }
}

if (!empty($whereOr)) {
  $where[] = implode(" OR ", $whereOr);
}

if ($date_min) {
  $where[] = "datetime >= '$date_min'";
}

if ($date_max) {
  $where[] = "datetime <= '$date_max'";
}

/** @var CConstantesMedicales[] $list_constantes */
// Les constantes qui correspondent (dans le contexte cette fois)
$list_constantes  = $constantes->loadList($where, "datetime DESC", $limit);
$total_constantes = $constantes->countList($where);

$const_ids = array();

foreach ($list_constantes as $_cst) {
  $const_ids[] = $_cst->_id;
  $_cst->loadRefsComments();

  if ($_cst->poids || $_cst->variation_poids) {
    /* On recalcule la variation du poids */
    $_cst->getWeightVariation();
    /* En cas de modification de la valeur,
     * si la constante a partir de laquelle a été calculée la variation est supprimée ou modifiée,
     * On enregistre la nouvelle valeur
     */
    if ($_cst->fieldModified('variation_poids')) {
      $_cst->rawStore();
    }
    if ($patient->_annees < 2) {
      $_cst->getVariationPoidsNaissance();
    }
  }
}

// Calcul du Bilan Hydrique
$bh_type   = CConstantesMedicales::$list_constantes["_bilan_hydrique"]["type"];
$is_hidden = isset($selection["all"]["hidden"]) && in_array("_bilan_hydrique", $selection["all"]["hidden"]) ||
  isset($selection[$bh_type]["hidden"]) && in_array("_bilan_hydrique", $selection[$bh_type]["hidden"]) ||
  !count(CMbArray::searchRecursive('_bilan_hydrique', $selection));

if ($context && $context instanceof CSejour && !$is_hidden) {
  /** @var CSejour $context */;
  $bh_cst = CConstantesMedicales::getValeursHydriques($context);

  $presc_sejour = $context->loadRefPrescriptionSejour();

  $bh_perfs = array();
  if ($presc_sejour && $presc_sejour->_id) {
    $bh_perfs = $presc_sejour->calculEntreesHydriques();
  }

  $bilan = CConstantesMedicales::calculBilanHydrique($bh_cst, $bh_perfs);

  foreach ($bilan as $_datetime => $_bilan) {
    $_id = $_bilan["id"];

    if ($_id === null) {
      $_new_cst                  = new CConstantesMedicales();
      $_new_cst->datetime        = $_datetime;
      $_new_cst->_bilan_hydrique = $_bilan["cumul"];

      array_unshift($list_constantes, $_new_cst);
    }

    if (!isset($list_constantes[$_id])) {
      continue;
    }

    $list_constantes[$_id]->_bilan_hydrique = $_bilan["cumul"];
    $list_constantes[$_id]->_title          = CAppUI::tr("CConstantesMedicales-msg-bilan_hydrique");

    /* Ajout du bilan hydrique dans les derniers valeurs des constantes */
    $latest_values                           = $latest_constantes[0];
    $latest_values->_bilan_hydrique          = $_bilan['cumul'];
    $latest_constantes[1]['_bilan_hydrique'] = $_datetime;
  }

  CMbArray::pluckSort($list_constantes, SORT_DESC, "datetime");
}

$list_constantes           = CConstantesMedicales::getConvertUnitGlycemie($list_constantes, false, $activate_choice_blood_glucose_units);
$constantes_medicales_grid = CConstantesMedicales::buildGrid($list_constantes, false, true, $host);

$list_constantes = array_reverse($list_constantes, true);

$context_guid_graph = $context_guid;
if ($selected_context_guid == 'all') {
  $context_guid_graph = $selected_context_guid;
}
$graph = new CConstantGraph($host, $context_guid_graph);
$graph->formatGraphDatas($list_constantes, [], $activate_choice_blood_glucose_units);

// Make blood sugar report
if ($context instanceof CSejour) {
    $constantes_service = new ConstantesService();

    $blood_sugar_reports = $constantes_service
        ->betweenDates(
            $date_min ? DateTime::createFromFormat("Y-m-d H:i:s", $date_min) : null,
            $date_max ? DateTime::createFromFormat("Y-m-d H:i:s", $date_max) : null
        )
        ->withChooseUnit($activate_choice_blood_glucose_units)
        ->getBloodSugarReport($context);
}

if (CModule::getActive("appFineClient") && CAppUI::gconf("appFineClient Sync allow_appfine_sync")) {
  if ($context instanceof CSejour || $context instanceof CConsultation) {
    $context->loadRefPatient();
    CAppFineClient::loadIdex($context->_ref_patient);
    $context->_ref_patient->loadRefStatusPatientUser();

    $count_order_no_treated = CAppFineClient::countOrderNotTreated($context, ['CExClass']);
  }
}

// Get the last unit Glycemie value selected
$constantes->unite_glycemie = $constantes->_unite_glycemie;
$ref_unit_glycemie = $activate_choice_blood_glucose_units ? CConstantesMedicales::getlastUnitGlycemie($list_constantes, true) : CAppUI::gconf('dPpatients CConstantesMedicales unite_glycemie');
$conv = CConstantesMedicales::getConv("glycemie", $ref_unit_glycemie);
$latest_constantes[0]->_glycemie = $latest_constantes[0]->glycemie = round($latest_constantes[0]->glycemie * $conv, CConstantesMedicales::CONV_ROUND_DOWN);

if ($activate_choice_blood_glucose_units) {
    $latest_constantes[0]->unite_glycemie = $ref_unit_glycemie;
    $constantes->unite_glycemie = $ref_unit_glycemie;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign('constantes',                 $constantes);
$smarty->assign('context',                    $context);
$smarty->assign('current_context',            $current_context);
$smarty->assign('context_guid',               $context_guid);
$smarty->assign('list_contexts',              $list_contexts);
$smarty->assign('all_contexts',               $selected_context_guid == 'all');
$smarty->assign('patient',                    $patient);
$smarty->assign('const_ids',                  $const_ids);
$smarty->assign('latest_constantes',          $latest_constantes);
$smarty->assign('selection',                  $selection);
$smarty->assign('custom_selection',           $custom_selection);
$smarty->assign('print',                      $print);
$smarty->assign('graphs_data',                $graph->graphs);
$smarty->assign('graphs_structure',           $graph->structure);
$smarty->assign('min_x_index',                $graph->min_x_index);
$smarty->assign('min_x_value',                $graph->min_x_value);
$smarty->assign('hidden_graphs',              $hidden_graphs);
$smarty->assign('display',                    $graph->display);
$smarty->assign('start',                      $start);
$smarty->assign('count',                      $count);
$smarty->assign('total_constantes',           $total_constantes);
$smarty->assign('paginate',                   $paginate);
$smarty->assign('constantes_medicales_grid',  $constantes_medicales_grid);
$smarty->assign('view',                       $view);
$smarty->assign('show_cat_tabs',              $show_cat_tabs);
$smarty->assign('can_edit',                   $can_edit);
$smarty->assign('can_select_context',         $can_select_context);
$smarty->assign('infos_patient',              $infos_patient);
$smarty->assign("mode_pdf",                   $mode_pdf);
$smarty->assign('date_min',                   $date_min);
$smarty->assign('date_max',                   $date_max);
$smarty->assign('unique_id',                  $unique_id);
$smarty->assign('iframe',                     $iframe);
$smarty->assign('blood_sugar',                $blood_sugar_reports);
$smarty->assign("count_order", $count_order_no_treated ?? 0);
$smarty->assign("ref_unit_glycemie", $ref_unit_glycemie);
$smarty->assign('activate_choice_blood_glucose_units', $activate_choice_blood_glucose_units);

$smarty->display('inc_vw_constantes_medicales');
