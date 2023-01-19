<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoireDHE;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

global $m, $tab;

CCanDo::checkEdit();
$protocole_id = CView::get("protocole_id", "ref class|CProtocole", true);
$chir_id      = CView::get("chir_id", "ref class|CMediusers", true);
CView::checkin();

$mediuser     = CMediusers::get();
$is_praticien = $mediuser->isPraticien();

if ($is_praticien) {
  $chir_id = $mediuser->user_id;
}

// Chargement du praticien
$chir = new CMediusers();
if ($chir_id) {
  $chir->load($chir_id);
}

// Vérification des droits sur les listes
$listPraticiens = $mediuser->loadPraticiens(PERM_EDIT);
$function = new CFunctions();
$listFunctions  = $function->loadSpecialites(PERM_EDIT);

$protocole = new CProtocole();
$protocole->load($protocole_id);

if ($protocole->_id) {
  // On vérifie que l'utilisateur a les droits sur le protocole
  if (!$protocole->getPerm(PERM_EDIT)) {
    CAppUI::setMsg("Vous n'avez pas accès à ce protocole", UI_MSG_WARNING);
    CAppUI::redirect("m=$m&tab=$tab&protocole_id=0"); 
  }
  $protocole->loadRefs();
  $protocole->loadRefsNotes();
  $protocole->loadRefPrescriptionChir();
  if ($protocole->function_id) {
    $protocole->_ref_function->loadRefGroup();
  }
  $chir =& $protocole->_ref_chir;
}
else {
  $protocole->temp_operation = '00:00:00';
}

// Durée d'une intervention
$start = CAppUI::conf("dPplanningOp COperation duree_deb");
$stop  = CAppUI::conf("dPplanningOp COperation duree_fin");
$step  = CAppUI::conf("dPplanningOp COperation min_intervalle");

// Récupération des services
$service = new CService();
$where = array();
$where["group_id"]  = "= '".CGroups::loadCurrent()->_id."'";
$where["cancelled"] = "= '0'";
$order = "nom";
$listServices = $service->loadListWithPerms(PERM_READ,$where, $order);

// Liste des types d'anesthésie
$listAnesthType = new CTypeAnesth();
$listAnesthType = $listAnesthType->loadGroupList();

$protocole->countContextDocItems();
$protocole->loadRefsProtocolesOp();
$protocole->formatCodageNGAP();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("mediuser"    , $mediuser);
$smarty->assign("is_praticien", $is_praticien);
$smarty->assign("protocole"   , $protocole);
$smarty->assign("chir"        , $chir);
$smarty->assign("ufs"         , CUniteFonctionnelle::getUFs());
$smarty->assign("cpi_list"    , CChargePriceIndicator::getList());
$smarty->assign("protocole_op_dhe", new CProtocoleOperatoireDHE());
$smarty->assign("listPraticiens", $listPraticiens);
$smarty->assign("listFunctions" , $listFunctions);
$smarty->assign("listServices"  , $listServices);
$smarty->assign("listAnesthType", $listAnesthType);

$smarty->display("vw_edit_protocole");
