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
use Ox\Core\CMbPath;
use Ox\Mediboard\Hospi\CCSVImportUniteFonctionnelleLink;

CCanDo::checkAdmin();

$dir = rtrim(CAppUI::conf('root_dir'), '/') . '/tmp/dPhospi';
CMbPath::forceDir($dir);
$filename = "$dir/import_uf_link.csv";

move_uploaded_file($_FILES['import_file']['tmp_name'], $filename);

$import = new CCSVImportUniteFonctionnelleLink($filename);
$import->import();

$msg_ok  = array();
$msg_err = array();
if ($nb_exists_hebergement = $import->getNbExistsHebergement()) {
  $msg_ok[] = "$nb_exists_hebergement liens d'UF d'hébergement existent déjà";
}
if ($nb_exists_soins = $import->getNbExistsSoins()) {
  $msg_ok[] = "$nb_exists_soins liens d'UF de soins existent déjà";
}
if ($nb_new_hebergement = $import->getNbHebergement()) {
  $msg_ok[] = "$nb_new_hebergement liens vers des UF d'hébergement ajoutés";
}
if ($nb_new_soins = $import->getNbSoins()) {
  $msg_ok[] = "$nb_new_soins liens vers des UF de soins ajoutés";
}

$msg_err = $import->getMsg();

if ($msg_err) {
  foreach ($msg_err as $_err) {
    CAppUI::stepAjax($_err, UI_MSG_WARNING);
  }
}

unlink($filename);

CAppUI::callbackAjax('window.parent.afterImport', $msg_ok, $msg_err);
CApp::rip();