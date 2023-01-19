<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\System\AccessLog\CAccessLog;

CCanDo::checkAdmin();

CApp::setTimeLimit(0);
CApp::setMemoryLimit("1024M");

$dry_run      = CValue::get("dry_run", false);
$object_class = CValue::get("object_class");

/** @var CAccessLog $object_class */
$object_class::aggregate($dry_run, true);

echo CAppUI::getMsg();
