<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;

/**
 * Move a file (id) to a mediboard object
 */
CCanDo::checkEdit();

$file_id          = CValue::get("object_id");
$file_class       = CValue::get("object_class");
$destination_guid = CValue::get("destination_guid");
$name             = CValue::get("file_name");
$category_id      = CValue::get("category_id");

$allowed = array("CFile", "CCompteRendu");

if (!in_array($file_class, $allowed)) {
  CAppUI::stepAjax("CFile-msg-not_allowed_object_to_move", UI_MSG_ERROR);
}

/** @var CFile|CCompteRendu $file */
$file = new $file_class();
$file->load($file_id);
$file->file_category_id = ($category_id && $category_id != $file->file_category_id) ? $category_id : $file->file_category_id;
if ($file instanceof CFile) {
  $file->file_name = $name ? $name : $file->file_name;
}

$destination = CStoredObject::loadFromGuid($destination_guid);
if (($file->object_id == $destination->_id) && ($file->object_class == $destination->_class)) {
  CAppUI::stepAjax("CFile-msg-from_equal_to", UI_MSG_ERROR);
}
$file->setObject($destination);

// check category
$cat = new CFilesCategory();
$cat->load($file->file_category_id);

if ($destination->_class != "CFileTraceability") {
    if ($cat->class && $cat->class != $destination->_class) {
        $file->file_category_id = "";
    }
}

if ($msg = $file->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg("CFile-msg-moved");
}
echo CAppUI::getMsg();
