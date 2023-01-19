<?php
/**
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFile;

CCanDo::checkRead();

$file_id = CValue::get("file_id");
$url     = CValue::get('url');

$format = CValue::get('format');

if (!$file_id && !$url) {
  return "";
}

if ($file_id) {
  $file = new CFile();
  $file->load($file_id);
  $file->canDo();
  if (!$file->_can->read) {
    return "";
  }

  //@TODO le faire marcher avec du datauri
  if (strpos($file->file_type, "svg") !== false) {
    CApp::json("?m=files&raw=thumbnail&document_guid=$file->_class-$file->_id");
  }
  elseif ($format === 'uri') {
    $data = $file->getDataURI();
    CApp::json($data);
  }
}
elseif ($url) {
  $mime_type = CMbPath::guessMimeType($url);
  $content   = @file_get_contents($url);
  if ($content) {
    $data = "data:" . $mime_type . ";base64," . urlencode(base64_encode($content));
    CApp::json($data);
  }
  else {
    return "";
  }
}