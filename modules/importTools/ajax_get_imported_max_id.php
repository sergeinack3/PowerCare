<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;

CCanDo::checkAdmin();

$class = CView::get('class', 'str notNull');

CView::checkin();

if (!$class || !class_exists($class)) {
    CAppUI::commonError();
}

/** @var CExternalDBImport $object */
$object = new $class();

if ($last_imported = $object->getImportedMaxID()) {
    CApp::log($last_imported);
    CAppUI::stepAjax('Table analysée', UI_MSG_OK);
} else {
    CAppUI::stepAjax('CMbObject.none', UI_MSG_WARNING);
}

CApp::rip();
