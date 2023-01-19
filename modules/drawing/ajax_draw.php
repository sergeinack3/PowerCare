<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Drawing\CDrawingCategory;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;

CCanDo::checkEdit();

$draw_id      = CValue::get('id');
$context_guid = CValue::get("context_guid");

$draw = new CFile();
$draw->load($draw_id);
$draw->loadRefsNotes();
$draw->loadRefAuthor();
$draw->loadRefsNotes();
$draw->getBinaryContent();

$user = CMediusers::get();
$user->loadRefFunction();
$functions = $user->loadRefsSecondaryFunctions();
$admin     = $user->isAdmin();

$files_in_context = array();
$object           = null;
if ($context_guid) {
  $object = CMbObject::loadFromGuid($context_guid);
  if ($object->_id) {
    $object->loadRefsFiles();
    if (CModule::getActive("oxCabinet") && $object->_class === "CEvenementPatient") {
        /** @var $object CEvenementPatient */
        $draw->file_category_id = CAppUI::gconf("oxCabinet CEvenementPatient categorie_{$object->type}_default");
    }
    foreach ($object->_ref_files as $file_id => $_file) {
      if ($_file->file_type === "image/fabricjs"
        || $_file->annule
        || strpos($_file->file_type, "image/") === false
      ) {
        unset($object->_ref_files[$file_id]);
      }
    }
  }
}

// creation
if (!$draw->_id) {

  // author = self
  $draw->author_id   = $user->_id;
  $draw->_ref_author = $user;
  $draw->file_type   = "image/svg+xml";
  $draw->file_name   = CAppUI::tr("common-Untitled");

  // context
  if ($object && $object->_id) {
    $draw->setObject($object);
  }
  // assign to user
  else {
    $draw->setObject($user);
  }

  $draw->loadTargetObject();
}

$file_categories = CFilesCategory::listCatClass($object->_class);

$category            = new CDrawingCategory();
$where               = array("user_id" => " = '$user->_id'");
$categories_user     = $category->loadList($where);
$where               = array("function_id" => " = '$user->function_id'");
$categories_function = $category->loadList($where);
$where               = array("group_id" => " = '$user->_ref_function->group_id'");
$categories_group    = $category->loadList($where);

/** @var CDrawingCategory[] $categories */
$categories = $categories_user + $categories_function + $categories_group;
foreach ($functions as $_function) {
  $where               = array("function_id" => " = '$_function->_id'");
  $categories_function = $category->loadList($where);
  $categories          = array_merge($categories, $categories_function);
}

foreach ($categories as $_category) {
  $_category->countFiles();
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("admin", $admin);
$smarty->assign("draw", $draw);
$smarty->assign("categories", $categories);
$smarty->assign("file_categories", $file_categories);
$smarty->assign("object", $object);
$smarty->display("inc_vw_draw");
