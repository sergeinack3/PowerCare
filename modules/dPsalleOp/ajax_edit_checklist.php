<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Récupération des paramètres
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\SalleOp\CDailyCheckList;

$date            = CView::get("date", 'date default|now', true);
$salle_id        = CView::get("salle_id", 'ref class|CSalle', true);
$bloc_id         = CView::get("bloc_id", 'ref class|CBlocOperatoire', true);
$type            = CView::get("type", "str default|ouverture_salle", true);
$sspi_id         = CView::get("sspi_id", "ref class|CSSPI", true);
$multi_ouverture = CView::get("multi_ouverture", 'bool default|0');

CView::checkin();

// Récupération de l'utilisateur courant
$user = CUser::get();
$currUser = new CMediusers();
$currUser->load($user->_id);
$currUser->isAnesth();
$currUser->isPraticien();

$salle = new CSalle();
$salle->load($salle_id);

$bloc = new CBlocOperatoire();
$bloc->load($bloc_id);

// Vérification de la check list journalière
$daily_check_lists = array();
$daily_check_list_types = array();
$check_list_by_types    = array();

$conf_required = 0;
if ($salle->_id && $salle->bloc_id != $bloc->_id) {
  $conf_required = $salle->loadRefBloc()->checklist_everyday;
}
elseif ($bloc->_id) {
  $conf_required = $bloc->checklist_everyday;
}
$group = CGroups::loadCurrent();
$conf_required_reveil = CAppUI::conf("dPsalleOp CDailyCheckList active_salle_reveil", $group);
$type_no_reveil = array('ouverture_salle', 'fermeture_salle');
$require_check_list = (($conf_required && in_array($type, $type_no_reveil))
    || ($conf_required_reveil && !in_array($type, $type_no_reveil))
    || ($salle->_id && $salle->cheklist_man))
    && $date >= CMbDT::date();

if ($require_check_list) {
  $object = $bloc->_id ? $bloc : $salle;
  list($check_list_not_validated, $daily_check_list_types, $daily_check_lists) = CDailyCheckList::getsCheckLists($object, $date, $type, $multi_ouverture, $sspi_id);
  //Permettre de faire plusieurs fois par jour les checklist d'ouverture de salle
  if ($multi_ouverture && !$check_list_not_validated) {
    $check_list_not_validated = count($daily_check_list_types);
    $list_type = array();
    foreach ($daily_check_lists as $key => $_daily_list_type) {
      $_list_type = new CDailyCheckList();
      $_list_type->object_class = $object->_class;
      $_list_type->object_id = $object->_id;
      $_list_type->list_type_id = $_daily_list_type->list_type_id;
      $_list_type->date = $date;
      $_list_type->type = null;
      $_list_type->loadMatchingObject("date, date_validate", "daily_check_list_id");
      if ($_list_type->validator_id) {
        $_list_type = new CDailyCheckList();
        $_list_type->object_class = $object->_class;
        $_list_type->object_id = $object->_id;
        $_list_type->list_type_id = $_daily_list_type->list_type_id;
        $_list_type->type = null;
        $_list_type->_id = null;
      }
      $_list_type->_ref_object = $object;
      $_list_type->loadRefListType()->loadRefsCategories();
      $_list_type->loadItemTypes();
      $_list_type->clearBackRefCache('items');
      $_list_type->loadBackRefs('items');
      $_list_type->loadRefListType();
      $list_type[] = $_list_type;
    }
    $daily_check_lists = $list_type;

    foreach ($daily_check_list_types as $_type) {
      $where = array();
      $where["object_class"] = " = '$object->_class'";
      $where["object_id"]    = " = '$object->_id'";
      $where["list_type_id"] = " = '$_type->_id'";
      $where["date"]         = " = '$date'";
      $where["type"]         = " IS NULL";
      $where["validator_id"] = " IS NOT NULL";
      $list_type = new CDailyCheckList();
      $check_list_by_types[$_type->_id] = $list_type->loadList($where, "date, date_validate", null, "daily_check_list_id");
    }
    $require_check_list = true;
  }

  if ($check_list_not_validated == 0) {
    $require_check_list = false;
  }
}

// Chargement des praticiens
$listAnesths = new CMediusers;
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

$listChirs = new CMediusers;
$listChirs = $listChirs->loadPraticiens(PERM_DENY);

$type_personnel = array("op", "op_panseuse", "iade", "sagefemme", "manipulateur");
if (count($daily_check_list_types) && $require_check_list) {
  $type_personnel = array();
  foreach ($daily_check_list_types as $check_list_type) {
    $validateurs = explode("|", $check_list_type->type_validateur);
    foreach ($validateurs as $validateur) {
      $type_personnel[] = $validateur;
    }
  }
}
$listValidateurs = CPersonnel::loadListPers(array_unique(array_values($type_personnel)), true, true);
$operateurs_disp_vasc = implode("-", array_merge(CMbArray::pluck($listChirs, "_id"), CMbArray::pluck($listValidateurs, "user_id")));

$nb_op_no_close = 0;
if ($type == "fermeture_salle") {
  $salle->loadRefsForDay($date);

  // Calcul du nombre d'actes codé dans les interventions
  if ($salle->_ref_plages) {
    foreach ($salle->_ref_plages as $_plage) {
      foreach ($_plage->_ref_operations as $_operation) {
        if (!$_operation->sortie_salle && !$_operation->annulee) {
          $nb_op_no_close++;
        }
      }
      foreach ($_plage->_unordered_operations as $_operation) {
        if (!$_operation->sortie_salle && !$_operation->annulee) {
          $nb_op_no_close++;
        }
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

// Daily check lists
$smarty->assign("salle"                 , $salle);
$smarty->assign("bloc"                  , $bloc);
$smarty->assign("type"                  , $type);
$smarty->assign("date"                  , $date);
$smarty->assign("nb_op_no_close"        , $nb_op_no_close);
$smarty->assign("require_check_list"    , $require_check_list);
$smarty->assign("daily_check_lists"     , $daily_check_lists);
$smarty->assign("daily_check_list_types", $daily_check_list_types);
$smarty->assign("listValidateurs"       , $listValidateurs);
$smarty->assign("listChirs"             , $listChirs);
$smarty->assign("listAnesths"           , $listAnesths);
$smarty->assign("multi_ouverture"       , $multi_ouverture);
$smarty->assign("check_list_by_types"   , $check_list_by_types);

$smarty->display("vw_edit_checklist.tpl");
