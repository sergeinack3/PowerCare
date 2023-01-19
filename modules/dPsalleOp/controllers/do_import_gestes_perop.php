<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CGestePeropXMLImport;

CCanDo::checkEdit();
$current_group = CView::post("current_group", "bool default|0");
$function_id   = CView::post("function_id", "ref class|CFunctions");
$user_id       = CView::post("user_id", "ref class|CMediusers");
$file          = CValue::files("formfile");
CView::checkin();

if (!$file || !$file["tmp_name"]) {
  CAppUI::stepAjax("CFile-msg-None file found", UI_MSG_ERROR);
}

if (!$current_group && !$function_id && !$user_id) {
  CAppUI::stepAjax("CGestePerop-msg-Please choose a context to import the different gestures", UI_MSG_ERROR);
}

$counter        = 0;
$context_import = null;

if ($current_group) {
  $context_import = CGroups::loadCurrent();
}
elseif ($function_id) {
  $context_import = CFunctions::find($function_id);
}
elseif ($user_id) {
  $context_import = CMediusers::find($user_id);
}

$files_path = rtrim(CAppUI::conf("root_dir")) . "/tmp/gestes_import";

$zip = new ZipArchive();
$zip->open($file["tmp_name"][0]);
$zip->extractTo($files_path);
$directories = glob($files_path . "/*");

foreach ($directories as $_directory) {
  $importer = new CGestePeropXMLImport($_directory . "/export.xml", $context_import);
  $importer->import();
  $counter = $importer->getCount("CGestePerop");
  $msg     = $importer->getMessages();

  CAppUI::setMsg($msg, $counter ? UI_MSG_OK : UI_MSG_ERROR);
}

if (is_dir($files_path)) {
  CMbPath::remove($files_path);
}

echo CAppUI::getMsg();
CApp::rip();
