<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Mediboard\Drawing\CDrawingCategory;
use Ox\Mediboard\Files\CFile;

$startTime = CMbDT::dateTime();
$category  = CValue::post("category", "divers");

//no zip lib
if (!function_exists("zip_open")) {
  CAppUI::stepAjax("CFile-msg-no_zip_method", UI_MSG_ERROR);
}

$file = isset($_FILES['zip']) ? $_FILES['zip'] : null;

if (!$file || !is_array($file)) {
  CAppUI::stepAjax("pas de fichier", UI_MSG_ERROR);
}
if ($file["type"] != "application/zip") {
  CAppUI::stepAjax("Le fichier n'est pas un zip", UI_MSG_ERROR);
}

$mb_file_name = $file["tmp_name"];

$zip = zip_open($mb_file_name);
while ($zipFile = zip_read($zip)) {
  $doc_name  = zip_entry_name($zipFile);
  $extension = strtolower(pathinfo($doc_name, PATHINFO_EXTENSION));
  $file_name = pathinfo($doc_name, PATHINFO_BASENAME);
  $doc_size  = zip_entry_filesize($zipFile);
  $dir_name  = explode("/", dirname($doc_name));
  $dir_name  = end($dir_name);
  if (((!$dir_name || $dir_name == ".") && $category) || $category != "divers") {
    $dir_name = $category;
  }

  $cat       = new CDrawingCategory();
  $cat->name = $dir_name;
  $cat->loadMatchingObjectEsc();
  if ($msg = $cat->store()) {
    CAppUI::stepAjax($msg, UI_MSG_WARNING);
  }

  $content_file = zip_entry_read($zipFile, $doc_size);
  if ($content_file) {
    $file            = new CFile();
    $file->file_name = $file_name;
    $file->author_id = CAppUI::$user->_id;
    $file->file_type = CMbPath::guessMimeType($doc_name);
    $file->fillFields();
    $file->setObject($cat);
    $file->updateFormFields();
    $file->setContent($content_file);

    if ($msg = $file->store()) {
      CAppUI::setMsg($msg, UI_MSG_WARNING);
    }
  }
}
zip_close($zip);