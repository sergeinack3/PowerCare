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
use Ox\Mediboard\OpenData\CCSVImportFiness;

CCanDo::checkRead();

$update = CView::post('update', 'bool default|0');
$geolocalisation = CView::post('geolocalisation', 'bool default|0');

CView::checkin();

$root_dir = rtrim(CAppUI::conf('root_dir'), '/');
$tmp_dir  = rtrim(CFile::getDirectory(), '/\\') . '/upload/Finess_structures_geolocalisation/';

if (!is_dir($tmp_dir)) {
  $zip_path = $root_dir . '/modules/openData/resources/Finess_structures_geolocalisation.zip';
  $zip = new ZipArchive();
  $zip->open($zip_path);
  $zip->extractTo($tmp_dir);
}

$file_path = $tmp_dir . '/finess_structures.csv';

$import = new CCSVImportFiness($file_path, $tmp_dir, $update, $geolocalisation);
$import->import();

echo CAppUI::getMsg();
