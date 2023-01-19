<?php /** @noinspection PhpUndefinedClassInspection */

/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;

CCanDo::checkAdmin();

CApp::setMemoryLimit("1024M");
CApp::setTimeLimit('600');

CRefCheck::firstFillTable();

echo CAppUI::getMsg();