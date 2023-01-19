*<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\CView;

CCanDo::checkAdmin();

$class = CView::get('class', 'str notNull');

CView::checkin();

if (!$class || !class_exists($class)) {
  CAppUI::commonError();
}

/** @var CExternalDBImport $object */
$object = new $class();

$object->analyze();
CAppUI::stepAjax("Table analysée", UI_MSG_OK);
CApp::rip();
