<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Mediboard\Mediusers\CCSVImportUniteFonctionnelleMedicaleLink;

CCanDo::checkAdmin();

$dir = rtrim(CAppUI::conf('root_dir'), '/') . '/tmp/mediusers';
CMbPath::forceDir($dir);
$filename = "$dir/import_ufm_link.csv";

move_uploaded_file($_FILES['import_file']['tmp_name'], $filename);

$import = new CCSVImportUniteFonctionnelleMedicaleLink($filename);
$import->import();

$msg_ok = array();
$msg_error = array();
if ($nb_ok = $import->getNbOk()) {
  $msg_ok[] = "$nb_ok liens d'UF médicale créés";
}
if ($nb_exists = $import->getNbExists()) {
  $msg_ok[] = "$nb_exists liens d'UF médicale existants";
}

$msg_error = $import->getMsg();

unlink($filename);

CAppUI::callbackAjax('window.parent.afterImport', $msg_ok, $msg_error);
CApp::rip();