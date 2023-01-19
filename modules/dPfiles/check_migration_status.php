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
use Ox\Core\CMbConfig;
use Ox\Mediboard\Files\CFile;

CCanDo::check();

$files = glob(CFile::getDirectory() . "/C*");
if (empty($files)) {
  CAppUI::setConf('dPfiles CFile migration_finished', 1);
}
else {
  CAppUI::setConf('dPfiles CFile migration_finished', 0);
}

$config_db = CAppUI::conf("config_db");
if ($config_db) {
  CMbConfig::loadValuesFromDB();
}

echo CAppUI::getMsg();
CApp::rip();
