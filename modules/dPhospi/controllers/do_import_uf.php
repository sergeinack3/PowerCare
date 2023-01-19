<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CCSVImportUf;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;

CCanDo::checkAdmin();

$date  = CMbDT::datetime();

$dir = 'tmp/dPhospi';
CMbPath::forceDir($dir);
$filename = "$dir/import_uf_$date.csv";

move_uploaded_file($_FILES['import_file']['tmp_name'], $filename);

$import = new CCSVImportUf($filename);
$import->import();

$msg = '';
if ($count = $import->getCount()) {
  $msg .= "<div class=\"info\">$count unités fonctionnelles importées</div>";
}

if ($found = $import->getFound()) {
  $msg .= "<div class=\"info\">$found unités fonctionnelles déjà existantes</div>";
}

if ($errors = $import->getErrors()) {
  $msg .= "<div class=\"error\">$errors erreurs</div>";
}

CAppUI::callbackAjax('window.parent.afterImport', $msg);
CApp::rip();