<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Interop\Hl7\CHL7v2Import;

CCanDo::checkAdmin();

$import = new CHL7v2Import();
$import->importDatabase();

foreach ($import->getMessages() as $message) {
    CAppUI::stepAjax(...$message);
}

CApp::rip();
