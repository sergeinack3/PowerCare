<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassField;

CCanDo::checkEdit();

$field_name = CView::post('field_name', 'str notNull');

CView::checkin();

$ex_field       = new CExClassField();
$ex_field->name = $field_name;
$ex_field->loadMatchingObjectEsc();

if (!$ex_field || !$ex_field->_id) {
  CAppUI::stepAjax('CExClassField-name not exists', UI_MSG_ERROR);
}

if (!$ex_field->getPerm(PERM_EDIT)) {
  $ex_field->needsEdit();
}

$ex_field->_regenerate = true;

if ($msg = $ex_field->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

CApp::rip();