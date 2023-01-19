<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$object_class     = CValue::post("object_class");
$object_id        = CValue::post("object_id");
$content          = CValue::post("content");
$file_name        = CValue::post("file_name");
$file_category_id = CValue::post("file_category_id");

$file = new CFile();
$file->file_name = $file_name;
$file->object_class = $object_class;
$file->object_id = $object_id;
$file->file_category_id = $file_category_id;
$file->author_id = CMediusers::get()->_id;

$file->fillFields();
$file->setContent(base64_decode($content));
$file->file_type = CMbPath::guessMimeType($file_name);

if ($msg = $file->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg("CFile-msg-moved");
}

echo CAppUI::getMsg();