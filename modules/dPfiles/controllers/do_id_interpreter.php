<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Files\CIdImageCropper;
use Ox\Mediboard\Files\CIdInterpreter;

CCanDo::checkEdit();
$file      = CValue::files("formfile");
$file_type = CView::post("file_type", "str default|id_card");

CView::checkin();

// No files
if (!$file || count($file["tmp_name"]) === 0) {
  echo json_encode(array(
    "error" => "no_files",
  ));
  CApp::rip();
}

// Not an image
if (strpos($file["type"][0], "image") !== 0) {
  echo json_encode(array(
    "error" => "not_an_image",
  ));
  CApp::rip();
}

// Get resized image
$data = array();
$image_crop = new CIdImageCropper($file["tmp_name"][0], $file_type);
$image_crop->cropWhiteSpaces();
$image_crop->scaleDown();
$data["image"] = $image_crop->getFaceBase64();
$data["image_cropped"] = $image_crop->getBase64();
$data["image_mime"] = $image_crop->mime;
$image_crop->cropByType();

// Get text image
$id_interpreter = new CIdInterpreter();
$id_interpreter->file_type = $file_type;

try {
  $text_data = $id_interpreter->decodeFile($file["tmp_name"][0]);
} catch (Exception $e) {
  echo json_encode(array("error" => "$e"));
  CApp::rip();
}

if ($text_data) {
  $data = array_merge($data, $text_data);
}

// No data error
if (!$data) {
  echo json_encode(array("error" => "an_error_occured"));
  CApp::rip();
}

// Additional text traitment
if (isset($data["sexe"])) {
  $data["sexe"] = strtolower($data["sexe"]);
  if (!in_array($data["sexe"], array("f", "m"))) {
    $data["sexe"] = "";
  }
}

if (isset($data["naissance"])) {
  $data["naissance"] = intval($data["naissance"]);
  if (strlen($data["naissance"]) < 6) {
    $data["naissance"] = "";
  }
  if ($data["naissance"]) {
    $dataYear = substr($data["naissance"], 0, 2);
    $dataYear = (($dataYear > date("y")) ? "19" : "20") . $dataYear;
    $dataMonth = substr($data["naissance"], 2, 2);
    $dataDay = substr($data["naissance"], 4, 2);

    if ($dataDay < 32 && $dataMonth < 13) {
      $data["naissance"] = "$dataDay/$dataMonth/$dataYear";
    }
  }
}

echo json_encode($data);
CApp::rip();
