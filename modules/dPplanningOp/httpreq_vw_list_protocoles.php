<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $dialog;

use Ox\AppFine\Client\CAppFineClientOrderPackProtocole;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\Sante400\CIdSante400;

if ($dialog) {
  CCanDo::checkRead();
}
else {
  CCanDo::checkEdit();
}

// L'utilisateur est-il chirurgien?
$chir_id      = CValue::getOrSession("chir_id");
$mediuser     = CMediusers::get($chir_id);
if (!$mediuser->isPraticien()) {
  $mediuser = new CMediusers();
}
$function_id  = CValue::getOrSession("function_id");
$type         = CValue::getOrSession("type", "interv");
$page         = CValue::get("page");
$sejour_type  = CValue::get("sejour_type");
$inactive     = CValue::get("inactive", "0");
$appFine_pack_id = CView::get("appFine_pack_id", "ref class|CAppFineClientOrderPack");

$tags_to_display     = CValue::get("tags_to_search", "");
$exclude_no_index = CValue::get("exclude_no_idx", false) === "true" ? true : false;
CView::checkin();

if($tags_to_display !== ""){
  $tags_to_display = explode("|",$tags_to_display);
}

$step = 30;

$protocole = new CProtocole();
$where = array();

$chir     = new CMediusers();
$chir->load($chir_id);
if ($chir->_id) {
  $chir->loadRefFunction();
  //Limite de la recherche des protocoles de DHE à l'établissement courant
  list($ljoinSecondary, $whereSecondary, $functions) = CProtocole::checkMultiEtab($chir->_ref_function);
  $chir->loadBackRefs("secondary_functions", null, null, null, $ljoinSecondary, null, "", $whereSecondary);
  foreach ($chir->_back["secondary_functions"] as $curr_sec_func) {
    $functions[] = $curr_sec_func->function_id;
  }
  $list_functions = implode(",", $functions);
  $where[] = "protocole.chir_id IN ('$chir->_id', '$chir->main_user_id') OR protocole.function_id IN ($list_functions)";
}
else {
  $where["function_id"] = " = '$function_id'";
}

if ($inactive == 0) {
  $where["protocole.actif"]       = "= '1'";
}

$where["for_sejour"] = $type == 'interv' ? "= '0'" : "= '1'";

if ($sejour_type) {
  $where["type"] = "= '$sejour_type'";
}

$order = "libelle_sejour, libelle, codes_ccam";

$list_protocoles  = $protocole->loadListWithPerms(PERM_READ, $where, $order, "{$page[$type]},$step");

CStoredObject::massLoadFwdRef($list_protocoles, "protocole_prescription_chir_id");
CStoredObject::massLoadBackRefs($list_protocoles, "context_doc");
CStoredObject::massLoadBackRefs($list_protocoles, "links_protocoles_op");
$context_docs = CStoredObject::massLoadBackRefs($list_protocoles, 'context_doc');
CStoredObject::massCountBackRefs($context_docs, 'documents');
CStoredObject::massCountBackRefs($context_docs, 'files');

$systeme_materiel_expert = CAppUI::gconf("dPbloc CPlageOp systeme_materiel") == "expert";

$tags_to_protocol = array();

//génération de la liste de tags d'identifiants externes à rechercher
if(is_countable($tags_to_display)){
  foreach($tags_to_display as $_tag){
    $tags_to_protocol[$_tag] = array();
  }
}

foreach ($list_protocoles as $prot_key => $_prot) {
  $_prot->loadExtCodesCCAM();
  $_prot->loadExtCodeCIM();
  $_prot->loadRefPrescriptionChir();
  $_prot->_count_docitems = $_prot->countContextDocItems();

  if ($systeme_materiel_expert == "expert") {
    $_prot->_types_ressources_ids = implode(",", CMbArray::pluck($_prot->loadRefsBesoins(), "type_ressource_id"));
  }

  if (CModule::getActive("appFineClient") && CAppUI::gconf("appFineClient Sync allow_appfine_sync")) {
    $_prot->_pack_appFine_ids = implode(
      ",", CMbArray::pluck(CAppFineClientOrderPackProtocole::loadRefsPacksProtocoleAppFine($_prot), "pack_id")
    );

      // Pack AppFine déjà rattaché à ce protocole ?
      if ($appFine_pack_id) {
          foreach (explode(',', $_prot->_pack_appFine_ids) as $_pack_id) {
              if ($_pack_id == $appFine_pack_id) {
                  $_prot->_pack_already_linked = true;
              }
          }
      }
  }

  $_prot->loadRefsDocItemsGuids();
  $_prot->loadRefsProtocolesOp();
  $_prot->loadEpisodeSoin();

  if (is_countable($tags_to_display)) {
    //Recherche des identifiants externes correspondant à chaque tag, pour chaque protocole
    foreach ($tags_to_protocol as $name_tag => $_tag){
      $idx = new CIdSante400();
      $idx->tag = $name_tag;
      $idx->setObject($_prot);
      $idx->loadMatchingObject();
      if($idx->_id){
        $tags_to_protocol[$name_tag][$_prot->_id] = $idx;
      }
      elseif ($exclude_no_index) {
        unset($list_protocoles[$prot_key]);
        $protocole->_totalWithPerms -= 1;
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("list_protocoles"   , $list_protocoles);
$smarty->assign("total_protocoles"  , $protocole->_totalWithPerms);
$smarty->assign("page"              , $page);
$smarty->assign("step"              , $step);
$smarty->assign("chir"              , $mediuser);
$smarty->assign("type"              , $type);
$smarty->assign("tags"              , $tags_to_protocol);
$smarty->assign("appFine_pack_id", $appFine_pack_id);

$smarty->display("inc_list_protocoles");
