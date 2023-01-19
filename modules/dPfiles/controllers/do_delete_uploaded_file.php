<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CUploader;

CCanDo::checkAdmin();

$filename = CValue::post("filename");
$temp     = CValue::post("temp");

if ($temp) {
  $result = CUploader::removeUploadedTemp($filename);
}
else {
  $result = CUploader::removeUploadedFile($filename);
}

CApp::json($result);