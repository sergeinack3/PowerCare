<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Interop\Eai\CAsipImport;
use Ox\Mediboard\Cim10\Cisp\CCIM10CispImport;

CCanDo::checkAdmin();

$import = new CAsipImport();
$import->importDatabase();

foreach ($import->getMessages() as $message) {
    CAppUI::stepAjax(...$message);
}

CApp::rip();
