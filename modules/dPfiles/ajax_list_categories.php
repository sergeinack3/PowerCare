<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkRead();

$page               = intval(CValue::get('page'  , 0));
$filter             = CValue::getOrSession("filter", "");
$eligible_file_view = CValue::getOrSession("eligible_file_view");
$class              = CValue::getOrSession("class");
$group              = CValue::getOrSession("group");

$step  = 25;
$order = "nom, class";

$where = array();
if ($eligible_file_view == "1") {
  $where["eligible_file_view"] = "= '1'";
}
if ($eligible_file_view == "0") {
  $where["eligible_file_view"] = "= '0'";
}

if ($class) {
  $where["class"] = "= '$class'";
}

if ($group) {
    $where["group_id"] = "= '$group'";
}

$category = new CFilesCategory();
if ($filter) {
  $categories       = $category->seek($filter, $where, "$page, $step", true, null, $order);
  $total_categories = $category->_totalSeek;
}
else {
  $categories       = $category->loadList($where, $order, "$page, $step");
  $total_categories = $category->countList($where);
}

CStoredObject::massCountBackRefs($categories, "default_cats");

/** @var CFilesCategory $_category */
foreach ($categories as $_category) {
  $_category->loadRefGroup();
  $_category->countRelatedReceivers();
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("categories"       , $categories);
$smarty->assign("total_categories" , $total_categories);
$smarty->assign("page"             , $page);
$smarty->assign("step"             , $step);
$smarty->display("inc_list_categories.tpl");
