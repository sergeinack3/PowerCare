<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;

$data     = CValue::post("data");
$filename = CValue::post("filename", "data");

$data = stripslashes($data);
$data = json_decode(utf8_encode($data), true);

$csv = new CCSVFile(null, "excel");

foreach ($data as $_line) {
  $csv->writeLine($_line);
}

$csv->stream($filename);

CApp::rip();
