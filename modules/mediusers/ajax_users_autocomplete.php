<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PorteDocuments\CFolder;

$field       = CView::get("field", "str");
$view_field  = CView::get("view_field", "str default|$field");
$input_field = CView::get("input_field", "str default|$view_field");
$show_view   = CView::get("show_view", "str default|false") == "true";
$praticiens  = CView::get("praticiens", "bool default|0");
$prof_sante  = CView::get("prof_sante", "bool default|0");
$rdv         = CView::get("rdv", "bool default|0");
$anesths     = CView::get("anesths", "bool default|0");
$compta      = CView::get("compta", "enum default|0 list|0|1|2");
$edit        = CView::get("edit", "bool default|0");
$keywords    = CView::get($input_field, "str");
$limit       = CView::get("limit", "num default|30");
$function_id = CView::get("function_id", "ref class|CFunctions");
$use_group   = CView::get("use_group", "bool default|1");
$group_id    = CView::get("group_id", "ref class|CGroups");
$current_mod = CView::get("current_mod", "str");
$inactivite  = CView::get("inactivite", "bool default|0");
$mater       = CView::get("mater", "bool default|0");
$with_urgentistes = CView::get("with_urgentistes", "bool default|1");

CView::checkin();
CView::enableSlave();

/** @var CMediusers $object */
$object = new CMediusers();

$use_edit = CAppUI::pref("useEditAutocompleteUsers");
if (!$edit && $use_edit) {
  $edit = 1;
}

// Droits sur les utilisateurs retournés
$permType = $edit ?
  PERM_EDIT :
  PERM_READ;

// Récupération de la liste des utilisateurs
if ($rdv) {
  $listUsers = $object->loadProfessionnelDeSanteByPref($permType, null, $keywords);
}
elseif ($prof_sante) {
  $listUsers = $object->loadProfessionnelDeSante($permType, null, $keywords);
}
elseif ($anesths) {
  $listUsers = $object->loadAnesthesistes($permType, null, $keywords);
}
elseif ($praticiens) {
  $listUsers = $object->loadPraticiens($permType, null, $keywords, false, true, $use_group, $group_id);
}
elseif ($mater) {
  $listUsers = $object->loadListFromType(["Chirurgien", "Sage Femme"], $permType, null, $keywords, true, false, false, $use_group, $group_id);
}
else {
  $listUsers = $object->loadUsers($permType, $function_id, $keywords, ($compta !== "2"));
}

if ($compta) {
  $listUsersCompta = CConsultation::loadPraticiensCompta(
    null,
    ($compta === "1")
  );
  foreach ($listUsers as $_user) {
    if (!isset($listUsersCompta[$_user->_id])) {
      unset($listUsers[$_user->_id]);
    }
  }
}

$service_urgence_id = CGroups::get($group_id)->service_urgences_id;
if (!$with_urgentistes && $service_urgence_id) {
  foreach ($listUsers as $_user) {
    if ($_user->function_id === $service_urgence_id) {
      unset($listUsers[$_user->_id]);
    }
  }
}

CMediusers::massLoadUfMedicale($listUsers);
CMediusers::massLoadUfMedicaleSecondaire($listUsers);

$functions = array();
foreach ($listUsers as $_user) {
  $functions[$_user->_ref_function->_id] = $_user->_ref_function;
}

// Remove excluded user in Porte documents
if (CModule::getActive('porteDocuments') && $current_mod == 'porteDocuments') {
  $excluded_users_ids = CFolder::getExcludedUsers();

  foreach ($listUsers as $_user) {
    if (in_array($_user->_id, $excluded_users_ids)) {
      unset($listUsers[$_user->_id]);
    }
  }
}

// Fin d'activité
if ($inactivite) {
  $now = CMbDT::date();

  foreach ($listUsers as $_user) {
    if ($_user->fin_activite && ($_user->fin_activite < $now)) {
      unset($listUsers[$_user->_id]);
    }
  }
}

CFunctions::massLoadUfMedicale($functions);
CFunctions::massLoadUfMedicaleSecondaire($functions);

$template = $object->getTypedTemplate("autocomplete");

// Création du template
$smarty = new CSmartyDP("modules/system");

$smarty->assign("matches", $listUsers);
$smarty->assign("field", $field);
$smarty->assign("view_field", $view_field);
$smarty->assign("show_view", $show_view);
$smarty->assign("template", $template);
$smarty->assign("nodebug", true);
$smarty->assign("input", "");

$smarty->display("inc_field_autocomplete");
