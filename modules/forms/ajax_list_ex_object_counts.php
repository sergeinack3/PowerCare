<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExLink;
use Ox\Mediboard\System\Forms\CExObject;

if (CExClass::inHermeticMode(false)) {
    CCanDo::checkAdmin();
} else {
    CCanDo::checkEdit();
}

$date_min        = CValue::getOrSession("date_min");
$date_max        = CValue::getOrSession("date_max");
$group_id        = CValue::getOrSession("group_id");
$concept_search  = CValue::get("concept_search"); // concept values
$reference_class = CValue::get("reference_class");
$reference_id    = CValue::get("reference_id");
$owner_id        = CValue::get("owner_id");

CView::enforceSlave();

CExClassField::$_load_lite = true;
CExObject::$_multiple_load = true;
CExObject::$_load_lite     = true;

$ex_class = new CExClass();
$ds       = $ex_class->getDS();

$where = array(
  "ex_link.group_id" => " = '$group_id'",
);

if ($owner_id) {
  $where['ex_link.owner_id'] = $ds->prepare('= ?', $owner_id);
}
$ljoin = array();

$search = null;
if ($concept_search) {
  $concept_search = stripslashes($concept_search);
  $search         = CExConcept::parseSearch($concept_search);
}

$ex_link = new CExLink();

$where["ex_link.datetime_create"] = "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'";

if ($reference_class && $reference_id) {
  $where["ex_link.object_class"] = $ds->prepare("=?", $reference_class);
  $where["ex_link.object_id"]    = $ds->prepare("=?", $reference_id);
}
else {
  $where["ex_link.level"] = "= 'object'";
}

$ljoin["ex_class"] = "ex_class.ex_class_id = ex_link.ex_class_id";

$fields = array(
  "ex_link.ex_class_id",
);

$counts = $ex_link->countMultipleList($where, null, "ex_link.ex_class_id", $ljoin, $fields);

if (!empty($search)) {
  $where["ex_class_field.concept_id"] = $ds->prepareIn(array_keys($search));

  $ljoin["ex_class_field_group"] = "ex_class_field_group.ex_class_id = ex_class.ex_class_id";
  $ljoin["ex_class_field"]       = "ex_class_field.ex_group_id = ex_class_field_group.ex_class_field_group_id";
  unset($where["user_log.object_class"]);
}

$ex_objects_counts = array();
foreach ($counts as $_row) {
  $_ex_class_id = $_row["ex_class_id"];

  $_count = $_row["total"];

  $_ex_link = new CExLink();

  if (!empty($search)) {
    $_ex_class = new CExClass();
    $_ex_class->load($_ex_class_id);

    $where["ex_link.ex_class_id"] = "= '$_ex_class_id'";

    $ljoin_orig = $ljoin;
    $where_orig = $where;

    $where["ex_class.ex_class_id"] = "= '$_ex_class_id'";
    $where                         = array_merge($where, $_ex_class->getWhereConceptSearch($search));

    $ljoin["ex_object_$_ex_class_id"] = "ex_object_$_ex_class_id.ex_object_id = ex_link.ex_object_id";

    $request = new CRequest();
    $request->addSelect("COUNT(DISTINCT ex_link.ex_link_id)");
    $request->addTable($_ex_link->_spec->table);
    $request->addWhere($where);
    $request->addGroup("ex_link.ex_class_id");
    $request->addLJoin($ljoin);
    $_count = $_ex_link->getDS()->loadResult($request->makeSelect());

    $where = $where_orig;
    $ljoin = $ljoin_orig;
  }

  if ($_count > 0) {
    $ex_objects_counts[$_ex_class_id] = $_count;
  }
}

$where      = array(
  "ex_class_id" => $ds->prepareIn(array_keys($ex_objects_counts)),
);
$ex_classes = $ex_class->loadList($where);

$smarty = new CSmartyDP("modules/forms");
$smarty->assign("ex_objects_counts", $ex_objects_counts);
$smarty->assign("ex_classes", $ex_classes);
$smarty->display("inc_list_ex_object_counts.tpl");
