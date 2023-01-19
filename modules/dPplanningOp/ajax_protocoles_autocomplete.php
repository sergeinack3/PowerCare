<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClientOrderPackProtocole;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;

$field                = CView::get("field", "str");
$view_field           = CView::get('view_field', "str default|$field");
$input_field          = CView::get('input_field', "str default|$view_field");
$keywords             = CView::get($input_field, "str");
$limit                = CView::get("limit", "num default|30");
$chir_id              = CView::get("chir_id", "ref class|CMediusers");
$function_id          = CView::get('function_id', "ref class|CFunctions");
$for_sejour           = CView::get("for_sejour", "str");
$search_all_protocole = CView::get("search_all_protocole", "bool default|0");

CView::checkin();
CView::enableSlave();

$object = new CProtocole();
$ds     = $object->getDS();

$where = array();
if ($chir_id && !$search_all_protocole) {
  $chir = CMediusers::get($chir_id);
  $chir->loadRefFunction();
  list($ljoinSecondary, $whereSecondary, $functions_ids) = CProtocole::checkMultiEtab($chir->_ref_function);
  $chir->loadBackRefs("secondary_functions", null, null, null, $ljoinSecondary, null, "", $whereSecondary);
  if (count($chir->_back["secondary_functions"])) {
    $functions_ids = array_merge($functions_ids, CMbArray::pluck($chir->_back["secondary_functions"], "function_id"));
  }
  $where[] = "(protocole.chir_id IN ('$chir->_id', '$chir->main_user_id') OR protocole.function_id ". CSQLDataSource::prepareIn($functions_ids).")";
}
elseif ($function_id && !$search_all_protocole) {
  $where["protocole.function_id"] = "= '$function_id'";
}
else {
  $group = CGroups::loadCurrent();
  $use_protocole_current_etab = CAppUI::conf("dPplanningOp CProtocole use_protocole_current_etab", $group);
  $curr_user = CMediusers::get();
  $use_edit = CAppUI::pref("useEditAutocompleteUsers");
  $prats = $curr_user->loadPraticiens($use_edit ? PERM_EDIT : PERM_READ);
  $fncs  = $curr_user->loadFonctions($use_edit ? PERM_EDIT : PERM_READ, $use_protocole_current_etab ? $group->_id : null);
  $where[] = "(protocole.chir_id ".CSQLDataSource::prepareIn(CMbArray::pluck($prats, "user_id")).
    " OR protocole.function_id ". CSQLDataSource::prepareIn(array_keys($fncs)).")";
}

if ($for_sejour !== null) {
  $where["for_sejour"] = "= '$for_sejour'";
}

if ($keywords == "") {
  $keywords = "%";
}

$where["protocole.actif"] = "= '1'";

$order = "libelle, libelle_sejour, codes_ccam";

/** @var CProtocole[] $matches */
$matches = $object->getAutocompleteListWithPerms(PERM_READ, $keywords, $where, $limit, null, $order);

CStoredObject::massLoadFwdRef($matches, "service_id");
CStoredObject::massLoadFwdRef($matches, "uf_soins_id");
CStoredObject::massLoadFwdRef($matches, "uf_medicale_id");
CStoredObject::massLoadFwdRef($matches, "uf_hebergement_id");
if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel")) {
  CStoredObject::massLoadBackRefs($matches, "besoins_ressources");
}
CStoredObject::massLoadBackRefs($matches, "context_doc");
CStoredObject::massLoadBackRefs($matches, "links_protocoles_op");

foreach ($matches as $protocole) {
  $protocole->loadRefUfs();
  $protocole->loadRefService();

  if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel")) {
    $protocole->_types_ressources_ids = implode(",", CMbArray::pluck($protocole->loadRefsBesoins(), "type_ressource_id"));
  }

  if (CModule::getActive("appFineClient")) {
    $protocole->_pack_appFine_ids = implode(
      ",", CMbArray::pluck(CAppFineClientOrderPackProtocole::loadRefsPacksProtocoleAppFine($protocole), "pack_id")
    );
  }

  $protocole->loadRefsDocItemsGuids();
  $protocole->loadRefsProtocolesOp();

}

$template = $object->getTypedTemplate("autocomplete");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("matches"   , $matches);
$smarty->assign("field"     , $field);
$smarty->assign("view_field", $view_field);
$smarty->assign("show_view" , 1);
$smarty->assign("template"  , $template);
$smarty->assign("nodebug"   , true);
$smarty->assign("input"     , null);
$smarty->display('../../system/templates/inc_field_autocomplete');
