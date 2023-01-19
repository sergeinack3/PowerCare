<?php
/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\OpenData\CCSVImportHospiDiag;

CCanDo::checkRead();

$annee  = CView::post('annee', 'enum list|' . implode('|', CCSVImportHospiDiag::$annees) . ' notNull');
$update = CView::post('update', 'bool default|0');

CView::checkin();

$root_dir = rtrim(CAppUI::conf('root_dir'), '/');
$tmp_dir  = rtrim(CFile::getDirectory(), '/\\') . '/upload/';

if (!is_dir($tmp_dir) || !is_dir($tmp_dir . $annee)) {
  $zip_path = $root_dir . '/modules/openData/resources/HospiDiag.zip';
  $zip = new ZipArchive();
  $zip->open($zip_path);
  $zip->extractTo($tmp_dir);
}

$dir_path  = $tmp_dir . 'HospiDiag/' . $annee;
$file_path = $dir_path . '/hd' . $annee . '.csv';

$import = new CCSVImportHospiDiag($file_path, $dir_path, $annee, $update);
$import->import();

echo CAppUI::getMsg();
