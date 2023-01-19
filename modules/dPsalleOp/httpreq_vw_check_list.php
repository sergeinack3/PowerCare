<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\SalleOp\CDailyCheckItemCategory;
use Ox\Mediboard\SalleOp\CDailyCheckList;
use Ox\Mediboard\SalleOp\CDailyCheckListGroup;

CCanDo::checkRead();

$check_list_id = CView::get("check_list_id", 'ref class|CDailyCheckList');

CView::checkin();

$check_list = new CDailyCheckList();
$check_list->load($check_list_id);
$check_list->loadRefsFwd();
$check_list->loadRefListType()->loadRefsCategories();
$check_list->loadItemTypes();
$check_list->clearBackRefCache('items');
$check_list->loadBackRefs('items');

$anesth_id = null;

if ($check_list->object_class == "COperation") {
  $interv = $check_list->_ref_object;
  $check_list->_ref_object->loadRefChir();
  $anesth_id = ($interv->anesth_id) ? $interv->anesth_id : $interv->loadRefPlageOp()->anesth_id;
}
$anesth = new CMediusers();
$anesth->load($anesth_id);

$type_personnel = array("op", "op_panseuse", "iade", "sagefemme", "manipulateur");
if (!$check_list->validator_id && $check_list->_id && !$check_list->_ref_list_type->check_list_group_id
    && !in_array($check_list->type, array_keys(CDailyCheckList::$types))) {
  $validateurs = explode("|", $check_list->_ref_list_type->type_validateur);
  $type_personnel = array();
  foreach ($validateurs as $valid) {
    $type_personnel[] = $valid;
  }
}
$personnel = CPersonnel::loadListPers(array_unique(array_values($type_personnel)), true, true);

// Chargement des praticiens
$listChirs = new CMediusers();
$listChirs = $listChirs->loadPraticiens(PERM_DENY);

// Chargement des anesths
$listAnesths = new CMediusers();
$listAnesths = $listAnesths->loadAnesthesistes(PERM_DENY);

$where = array();
$ljoin = array();
if ($check_list->_ref_list_type->type == "fermeture_salle") {
  $where["daily_check_item_category.list_type_id"] = " = '$check_list->list_type_id'";
}
else {
  $where["daily_check_item_category.target_class"] = " = '$check_list->object_class'";
  if (!$check_list->_ref_list_type->check_list_group_id && $check_list->type) {
    $where["daily_check_item_category.type"] = " = '$check_list->type'";
  }
}

$ljoin["daily_check_list_type"] = "daily_check_list_type.daily_check_list_type_id = daily_check_item_category.list_type_id";
if ($check_list->_ref_list_type->check_list_group_id) {
  $where["daily_check_list_type.check_list_group_id"] = "= '".$check_list->_ref_list_type->check_list_group_id."'";
}
else {
  $where[] = "daily_check_list_type.check_list_group_id IS NULL";
}

$check_item_category = new CDailyCheckItemCategory();
$group_by = "daily_check_item_category.daily_check_item_category_id";
$check_item_categories = $check_item_category->loadList($where, "title", null, $group_by, $ljoin);

$last_item = false;
if ($check_list->_ref_list_type->check_list_group_id) {
  $checklist_group = new CDailyCheckListGroup();
  $checklist_group->load($check_list->_ref_list_type->check_list_group_id);
  $checklist_group->loadRefChecklist();
  if (end($checklist_group->_ref_check_liste_types)->_id == $check_list->list_type_id) {
    $last_item = true;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("check_list"           , $check_list);
$smarty->assign("personnel"            , $personnel);
$smarty->assign("list_chirs"           , $listChirs);
$smarty->assign("list_anesths"         , $listAnesths);
$smarty->assign("anesth_id"            , $anesth_id);
$smarty->assign("anesth"               , $anesth);
$smarty->assign("check_item_categories", $check_item_categories);
$smarty->assign("last_item"            , $last_item);
$smarty->display("inc_edit_check_list.tpl");
