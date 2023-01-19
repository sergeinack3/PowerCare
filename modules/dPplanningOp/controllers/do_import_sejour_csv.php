<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\PlanningOp\CCSVImportSejoursFormateurs;

CCanDo::checkAdmin();
CApp::setTimeLimit(600);

$filePath = isset($_FILES['import']["tmp_name"]) ? $_FILES['import']["tmp_name"] : null;

$import    = new CCSVImportSejoursFormateurs($filePath);
$nb_errors = $import->import();

if ($nb_errors) {
  CAppUI::setMsg("CCSVImportSejoursFormateurs-nb-errors", UI_MSG_WARNING, $nb_errors);
}

echo CAppUI::getMsg();