<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileUserView;

CCanDo::checkRead();

$user = CUser::get();

$object_guid = CValue::get("object_guid");

$object = CMbObject::loadFromGuid($object_guid);

$category = new CFilesCategory();
$category->eligible_file_view = 1;
$categories = $category->loadMatchingList();

$nb_unread = 0;
foreach ($categories as $_cat) {
  $file = new CFile();
  $file->file_category_id = $_cat->_id;
  $file->setObject($object);
  /** @var CFile[] $files */
  $_cat->_ref_files = $file->loadMatchingList();

  foreach ($_cat->_ref_files as $file_id => $_file) {
    $_file->loadRefReadStatus($user->_id);
    if (!$_file->_ref_read_status->_id) {
      $nb_unread ++;
    }
    else {
      unset($_cat->_ref_files[$file_id]);
      continue;
    }
  }
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("nb_unread", $nb_unread);
$smarty->assign("user_id", $user->_id);
$smarty->assign("file_view", new CFileUserView());
$smarty->assign("categories", $categories);
$smarty->assign("object", $object);
$smarty->display("inc_list_object_files_category.tpl");