<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;

CCanDo::checkAdmin();

$tmpFilesPath = CAppUI::conf("root_dir"). "/tmp/files";
CMbPath::remove($tmpFilesPath);

CAppUI::setMsg("L'arborescense a bien été supprimée", UI_MSG_OK);
echo CAppUI::getMsg();
CApp::rip();