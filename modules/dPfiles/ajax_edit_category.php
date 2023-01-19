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
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$category_id = CView::get("category_id", "ref class|CFilesCategory");
CView::checkin();

$category = new CFilesCategory();
$category->load($category_id);
$category->countDocItems();
$category->loadRefsNotes();

$classes = array();
if (!$category->_count_doc_items) {
  $listClass = CApp::getChildClasses(CMbObject::class, false, true);
  foreach ($listClass as $key => $_class) {
    $classes[$_class] = CAppUI::tr($_class);
  }
  asort($classes);
}

$groups = CGroups::loadGroups();

$smarty = new CSmartyDP();
$smarty->assign("category" , $category);
$smarty->assign("listClass", $classes);
$smarty->assign("groups"   , $groups);
$smarty->assign('can_dispatch', (bool)(CMediusers::get()->isAdmin() && $category->group_id === null));
$smarty->display("inc_form_category");
