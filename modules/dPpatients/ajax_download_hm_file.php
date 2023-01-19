<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$group = CGroups::loadCurrent();

$file_name = 'export-hm-' . $group->text;
$file_path = rtrim(CAppUI::conf('root_dir'), '/\\') . '/tmp/' . $file_name;

if (!file_exists($file_path)) {
  CAppUI::stepAjax('dPpatients-export-hm-file-not-exists', UI_MSG_ERROR);
}

header('Content-Description: File Transfert');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);

CApp::rip();