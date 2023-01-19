<?php

/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CCsarrImport;

CCanDo::checkAdmin();
CView::checkin();

$import = new CCsarrImport();
$import->importDatabase();

foreach ($import->getMessages() as $message) {
    CAppUI::stepAjax(...$message);
}

CApp::rip();
