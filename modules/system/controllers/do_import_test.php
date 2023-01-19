<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Tests\CTestXMLImport;

CCanDo::check();

$importClass = CValue::post("importClass", CTestXMLImport::class);
$importClass = stripslashes($importClass);

$filePath = __DIR__ . "/../../" . str_replace("..", "", CValue::post("filePath"));

if (!is_file($filePath)) {
  CAppUI::stepAjax("Wrong file path: '$filePath'", UI_MSG_ERROR);
}

/** @var CMbXMLObjectImport $import */
$import = new $importClass($filePath);
try {
  $import->import(array(), array());
}
catch (Exception $e) {
  CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
}

echo CAppUI::getMsg();
CApp::rip();
