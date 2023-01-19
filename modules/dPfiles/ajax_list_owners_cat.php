<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFilesCatDefault;

CCanDo::checkEdit();

$type             = CView::get("type", "enum list|users|functions");
$file_category_id = CView::get("file_category_id", "ref class|CFilesCategory");

CView::checkin();

$file_cat_default = new CFilesCatDefault();

switch ($type) {
  case "users":
    $file_cat_default->owner_class = "CMediusers";
    break;
  case "functions":
    $file_cat_default->owner_class = "CFunctions";
}

$file_cat_default->file_category_id = $file_category_id;

/** @var CFilesCatDefault[] $file_cat_default_list */
$file_cat_default_list = $file_cat_default->loadMatchingList();

$owners = CStoredObject::massLoadFwdRef($file_cat_default_list, "owner_id");
if ($type == "users") {
  CStoredObject::massLoadFwdRef($owners, "function_id");
}

foreach ($file_cat_default_list as $_file_cat_default_list) {
  $_file_cat_default_list->loadRefOwner();
  if ($type == "users") {
    $_file_cat_default_list->_ref_owner->loadRefFunction();
  }
}

$file_sorted = CMbArray::pluck($file_cat_default_list, "_ref_owner", "_view");
array_multisort($file_sorted, SORT_ASC, $file_cat_default_list);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("file_cat_default_list", $file_cat_default_list);
$smarty->assign("type"                 , $type);

$smarty->display("inc_list_owners_cat.tpl");
