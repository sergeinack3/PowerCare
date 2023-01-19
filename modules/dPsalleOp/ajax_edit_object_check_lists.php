<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CDailyCheckItemCategory;
use Ox\Mediboard\SalleOp\CDailyCheckList;

$object_guid    = CView::get("object_guid", 'str');
$type_group     = CView::get("type_group", 'str');
$validateur_ids = CView::get("validateur_ids", 'str');

CView::checkin();

if (!is_array($validateur_ids)) {
  $validateur_ids = explode("-", $validateur_ids);
  CMbArray::removeValue("", $validateur_ids);
}

if (count($validateur_ids)) {
  // Pas utiliser IN car on les souhaite dans l'ordre spécifié
  $validateurs = array();
  
  foreach ($validateur_ids as $_id) {
    $validateur = new CMediusers;
    $validateur->load($_id);
    $validateurs[$_id] = $validateur;
  }
}
else {
  $validateurs = array();
}

$object = CMbObject::loadFromGuid($object_guid);

// Chargement des 3 check lists de l'OMS
$check_lists = array();
$check_item_categories = array();

$check_list = new CDailyCheckList;
$cat = new CDailyCheckItemCategory;
$cat->target_class = $object->_class;

// Pre-anesth, pre-op, post-op
foreach ($check_list->_specs["type"]->_list as $_type) {
  if (CDailyCheckList::$types[$_type] != $type_group) {
    continue;
  }
  
  $list = CDailyCheckList::getList($object, null, $_type);
  $list->loadItemTypes();
  $list->loadRefsFwd();
  $list->loadBackRefs('items');
  $list->isReadonly();
  
  if ($list->_ref_object instanceof COperation) {
    $list->_ref_object->loadRefPraticien();
  }
  
  $check_lists[$_type] = $list;
  
  $cat->type = $_type;
  $check_item_categories[$_type] = $cat->loadMatchingList("title");
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("check_item_categories", $check_item_categories);
$smarty->assign("check_lists", $check_lists);
$smarty->assign("type_group", $type_group);
$smarty->assign("validateurs_list", $validateurs);
$smarty->display("inc_edit_object_check_lists.tpl");
