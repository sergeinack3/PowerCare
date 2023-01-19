<?php

/**
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Cim10\Gm\CCIM10GmImport;
use Ox\Mediboard\OpenData\CHospiDiagImport;

CCanDo::checkAdmin();

$import = new CHospiDiagImport();
$import->importDatabase();

foreach ($import->getMessages() as $message) {
    CAppUI::stepAjax(...$message);
}

CApp::rip();
