<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CEvenementPatient;

$file_id      = CValue::post("file_id");
$content      = CValue::post("svg_content");
$del          = CValue::post("del", 0);
$export       = CValue::post("export", 0);
$remove_draft = CValue::post("remove_draft", 0);

$file = new CFile();
$file->load($file_id);
$file->bind($_POST);
if ($export) {
  $file->_id = null;
}
$file->fillFields();
$file->loadTargetObject();
$file->updateFormFields();
if (CModule::getActive("oxCabinet") && $file->object_class === "CEvenementPatient") {
    /** @var $object CEvenementPatient */
    $file->file_category_id = CAppUI::gconf("oxCabinet CEvenementPatient categorie_{$file->_ref_object->type}_default");
}

if ($del) {
  if ($msg = $file->delete()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
  else {
    CAppUI::stepAjax("CFile-msg-delete", UI_MSG_OK);
  }
}
else {
  $file->file_type = "image/fabricjs";

  if ($export) {
    $svg            = new CFile();
    $svg->file_name = $file->file_name;
    $svg->file_type = "image/svg+xml";
    $svg->author_id = $file->author_id;
    $svg->file_category_id = $file->file_category_id;
    $svg->loadMatchingObject();
    $svg->fillFields();
    $svg->setObject($file->_ref_object);
    $svg->updateFormFields();
    if (strpos($svg->file_name, ".") === false) {
      $svg->file_name = $svg->file_name . ".svg";
    }
    if (strpos($svg->file_name, ".fjs") !== false) {
      $svg->file_name = str_replace(".fjs", ".svg", $svg->file_name);
    }
    // @TODO : replace url by datauri

    $content = str_replace(
      array("&raw=thumbnail", "&document_id", "&document_class"),
      array("&amp;raw=thumbnail", "&amp;document_id", "&amp;document_class"), $content
    );

    $svg->setContent(stripslashes($content));

    if ($msg = $svg->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::stepAjax("Dessin exporté avec succès", UI_MSG_OK);
    }

    if ($remove_draft) {
      $msg = $file->delete();
      CAppUI::stepAjax($msg ? $msg : "CFile-msg-delete", $msg ? UI_MSG_WARNING : UI_MSG_OK);
    }
  }
  // draft store
  else {
    $file->setContent(stripslashes($content));
    // no extensio;
    if (strpos($file->file_name, ".") === false) {
      $file->file_name .= ".fjs";
    }
    if ($msg = $file->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
  }
}
