<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExLink;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkRead();

$reference_class = CView::get("reference_class", "str notNull");
$reference_id    = CView::get("reference_id", "ref class|CMbObject meta|reference_class notNull");
$ex_class_id     = CView::get("ex_class_id", "ref class|CExClass notNull");
$event_name      = CView::get("event_name", "str notNull");
$tab_show_header = CView::get("tab_show_header", "bool notNull default|1");
$readonly        = CView::get("readonly", "bool default|0");
$tab_id          = CView::get("tab_id", "str notNull");

CView::checkin();

/** @var CMbObject $reference */
$reference = new $reference_class();
$reference->load($reference_id);

CExClassField::$_load_lite = true;
CExObject::$_multiple_load = true;
CExObject::$_load_lite     = true;

$ex_class = new CExClass();
$ex_class->load($ex_class_id);

$where                         = array();
$where["ex_link.object_class"] = "= '$reference_class'";
$where["ex_link.object_id"]    = "= '$reference_id'";
$where["ex_link.ex_class_id"]  = "= '$ex_class_id'";
$where["ex_link.level"]        = "= 'object'";

$ljoin             = array();
$ljoin["ex_class"] = "ex_class.ex_class_id = ex_link.ex_class_id";

$order = "ex_link.ex_object_id DESC";

/** @var CExLink[] $links */
$ex_link = new CExLink();
$links   = $ex_link->loadList($where, $order, null, "ex_link.ex_object_id", $ljoin);

CExLink::massLoadExObjects($links);

/** @var CExObject[] $ex_objects */
$ex_objects = array();

foreach ($links as $_link) {
  $_ex               = $_link->loadRefExObject();
  $_ex->_ex_class_id = $_link->ex_class_id;

  $ex_objects[$_link->ex_object_id] = $_ex;
}

// Création du template
$smarty = new CSmartyDP("modules/forms");
$smarty->assign("reference_class", $reference_class);
$smarty->assign("reference_id", $reference_id);
$smarty->assign("event_name", $event_name);
$smarty->assign("reference", $reference);
$smarty->assign("ex_objects", $ex_objects);
$smarty->assign("ex_links", $links);
$smarty->assign("ex_class", $ex_class);
$smarty->assign("tab_show_header", $tab_show_header);
$smarty->assign("readonly", $readonly);
$smarty->assign("tab_id", $tab_id);
$smarty->display("inc_display_ex_object_tab.tpl");
