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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CDailyCheckList;

CCanDo::checkRead();
$_type         = CView::get('_type', "str", true);
$type          = CView::get('type', "str", true);
$date_min      = CView::get('_date_min', 'date default|now', true);
$date_max      = CView::get('_date_max', 'date', true);
$object_guid   = CView::get('object_guid', 'str', true);
$check_list_id = CView::get('check_list_id', 'ref class|CDailyCheckList', true);
$start         = (int) CView::get('start', 'num');

CView::checkin();

$check_list = new CDailyCheckList;
$check_list->load($check_list_id);
$check_list->loadRefValidator2();
$check_list->loadRefListType();
$check_list->loadItemTypes();

if ($check_list->_ref_object) {
  $check_list->_ref_object->loadRefsFwd();
}

@list($object_class, $object_id) = explode('-', $object_guid);

$group_id = CGroups::loadCurrent()->_id;

$ljoin = array();
$where = array(
  "validator_id" => "IS NOT NULL",
  "daily_check_list.group_id"     => "= '$group_id'",
);
if ($_type) {
  $ljoin["daily_check_list_type"]     = "daily_check_list_type.daily_check_list_type_id = daily_check_list.list_type_id";
  $where["daily_check_list_type.type"]= " = '$_type'";
}
if ($type) {
  $where["daily_check_list.type"]= " = '$type'";
}

if ($object_class) {
  $where['daily_check_list.object_class'] = "= '$object_class'";
  if ($object_id) {
    $where['daily_check_list.object_id'] = "= '$object_id'";
  }
}
if ($date_min) {
  $where[] = "date >= '$date_min'";
}
if ($date_max) {
  $where[] = "date <= '$date_max'";
}

/** @var CDailyCheckList[] $list_check_lists */
$list_check_lists = $check_list->loadList($where, 'date DESC, date_validate DESC, object_class, object_id, type' , "$start,40", null, $ljoin);
$count_check_lists = $check_list->countList($where, null, $ljoin);

foreach ($list_check_lists as $_check_list) {
  $_check_list->loadRefListType();
  if ($_check_list->_ref_object) {
    $_check_list->_ref_object->loadRefsFwd();
  }
}

$check_list_filter = new CDailyCheckList();
$check_list_filter->object_class = $object_class;
$check_list_filter->object_id = $object_id;
$check_list_filter->_date_min = $date_min;
$check_list_filter->_date_max = $date_max;
$check_list_filter->_type     = $_type;
$check_list_filter->type      = $type;
$check_list_filter->loadRefsFwd();

$list_rooms = CDailyCheckList::getRooms();

$empty = new COperation();
$empty->updateFormFields();
$list_rooms["COperation"] = array($empty);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("list_check_lists"  , $list_check_lists);
$smarty->assign("count_check_lists" , $count_check_lists);
$smarty->assign("list_rooms"        , $list_rooms);
$smarty->assign("check_list"        , $check_list);
$smarty->assign("object_guid"       , $object_guid);
$smarty->assign("check_list_filter" , $check_list_filter);
$smarty->assign("start"             , $start);
$smarty->display("vw_daily_check_traceability.tpl");
