<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Sante400\CMouvFactory;
use Ox\Mediboard\Sante400\CTriggerMark;

CCanDo::checkRead();

$filter                 = new CTriggerMark();
$filter->mark_id        = CView::get("mark_id", "ref class|CTriggerMark", true);
$filter->trigger_class  = CView::get("trigger_class", "str", true);
$filter->trigger_number = CView::get("trigger_number", "numchar maxLength|10", true);
$filter->mark           = CView::get("mark", "str", true);
$filter->done           = CView::get("done", "bool", true);
$filter->_date_min      = CView::get("_date_min", "dateTime", true);
$filter->_date_max      = CView::get("_date_max", "dateTime", true);
$pagination             = array(
  "start" => CView::get("pagination_start", "num default|0"),
  "step"  => 30,
);

CView::checkin();

$trigger_classes = CMouvFactory::getClasses();

// Selected mark
$mark = new CTriggerMark();
$mark->load($filter->mark_id);

// filtered marks
$where = array();
if ($filter->trigger_class) {
  $where["trigger_class"] = "= '$filter->trigger_class'";
}

if ($filter->trigger_number) {
  $where["trigger_number"] = "= '$filter->trigger_number'";
}

if ($filter->mark) {
  $where["mark"] = "= '$filter->mark'";
}

if ($filter->done !== "") {
  $where["done"] = "= '$filter->done'";
}

if ($filter->_date_min) {
  $where["when"] = ">= '$filter->_date_min'";
}

if ($filter->_date_max) {
  $where["when"] = "<= '$filter->_date_max'";
}


$pagination["total"] = $filter->countList($where);
$order               = "`trigger_number` DESC";
$limit               = "{$pagination['start']}, {$pagination['step']}";
$marks               = $filter->loadList($where, $order, $limit);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("trigger_classes", $trigger_classes);
$smarty->assign("mark", $mark);
$smarty->assign("filter", $filter);
$smarty->assign("marks", $marks);
$smarty->assign("pagination", $pagination);
$smarty->display("view_marks.tpl");

