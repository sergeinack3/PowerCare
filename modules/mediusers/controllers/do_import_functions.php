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
use Ox\Mediboard\Mediusers\CCSVImportFunctions;

CCanDo::checkAdmin();

$dir = rtrim(CAppUI::conf('root_dir'), '/') . '/tmp/functions';
CMbPath::forceDir($dir);
$filename = "$dir/import_functions.csv";

move_uploaded_file($_FILES['import_file']['tmp_name'], $filename);

$import = new CCSVImportFunctions($filename);
$import->import();

$msg_ok = $import->getMsgOk();
$msg_ok[] = CAppUI::tr('CCSVImportFunctions-msg-new-%d', $import->getNbNewFunctions());
$msg_ok[] = CAppUI::tr('CCSVImportFunctions-msg-new-link-%d', $import->getNbNewLink());
$msg_err = array();


$msg_err = $import->getMsgError();

unlink($filename);

CAppUI::callbackAjax('window.parent.afterImport', $msg_ok, $msg_err);
CApp::rip();
