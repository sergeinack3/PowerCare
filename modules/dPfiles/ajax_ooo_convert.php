<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;

$file_data = base64_decode(CValue::post("file_data"));

$pdf_path  = CValue::post("pdf_path");

$temp_name_from = tempnam("./tmp", "from");
file_put_contents($temp_name_from, $file_data);

$path_python = CAppUI::conf("dPfiles CFile python_path") ? CAppUI::conf("dPfiles CFile python_path") ."/": "";
$res = exec("{$path_python}python ./modules/dPfiles/script/doctopdf.py {$temp_name_from} {$pdf_path}");

@unlink($temp_name_from);

echo $res;
