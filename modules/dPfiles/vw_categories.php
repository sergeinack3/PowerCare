<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$category_id        = CView::get("category_id", "ref class|CFilesCategory", true);
$page               = CView::get("page", "num default|0");
$filter             = CView::get("filter", "str", true);
$eligible_file_view = CView::get("eligible_file_view", "bool", true);
$class              = CView::get("class", "str", true);

CView::checkin();

$listClass = CApp::getChildClasses(CMbObject::class, false, true);

$etablissement  = new CGroups();
$etablissements = $etablissement->loadGroups();

$groups = [];
foreach ($etablissements as $key => $_etablissement) {
    $groups[$key] = $_etablissement->_view;
}


$classes = array();
foreach ($listClass as $key => $_class) {
  $classes[$_class] = CAppUI::tr($_class);
}
CMbArray::naturalSort($classes);

$smarty = new CSmartyDP();
$smarty->assign("category_id"       , $category_id);
$smarty->assign("page"              , $page);
$smarty->assign("listClass"         , $classes);
$smarty->assign("filter"            , $filter);
$smarty->assign("class"             , $class);
$smarty->assign("groups"            , $groups);
$smarty->assign("eligible_file_view", $eligible_file_view);
$smarty->display("vw_categories.tpl");

