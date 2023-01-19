<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cim10\Oms\CCIM10OmsImport;

CCanDo::checkAdmin();

$action = CView::post('action', 'enum list|import|update default|update');

CView::checkin();
CApp::setTimeLimit(360);

$import = new CCIM10OmsImport();
$import->importDatabase([], $action);

foreach ($import->getMessages() as $message) {
    CAppUI::stepAjax(...$message);
}

CApp::rip();
