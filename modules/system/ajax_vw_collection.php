<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CObjectNavigation;

CCanDo::checkAdmin();

$start = CView::get("start", "num default|0");
$step  = CView::get("step", "num default|50");

$class_name    = CView::get("object_class", "str notNull");
$class_id      = CView::get("object_id", "str notNull");
$back_ref_name = CView::get("back_ref_name", "str notNull");
$form_uid      = CView::get("form_uid", "str");

CView::checkin();

$obj_nav = new CObjectNavigation($class_name, $class_id);
$obj_nav->object_select->countAllBackRefs();

$obj_nav->object_select->loadBackRefs($back_ref_name, null, "$start,$step");

$smarty = new CSmartyDP();
$smarty->assign("start", $start);
$smarty->assign("counts", $obj_nav->object_select->_count);
$smarty->assign("object_select", $obj_nav->object_select);
$smarty->assign("back_name", $back_ref_name);
$smarty->assign("change_page_arg", "{$back_ref_name}_{$form_uid}");

$smarty->display("inc_collection_items.tpl");
