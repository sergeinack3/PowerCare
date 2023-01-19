<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();

$object_class = CView::get('object_class', 'str notNull');
$object_id    = CView::get('object_id', 'ref meta|object_class notNull');
$event_name   = CView::get('event_name', 'str notNull');
$after_save   = CView::get('after_save', 'str');

CView::checkin();

$object = CStoredObject::loadFromGuid("{$object_class}-{$object_id}");

if (!$object || !$object->_id) {
  CAppUI::commonError();
}

$ex_class_events = CExClassEvent::getForObject($object, $event_name, 'required');

switch ($after_save) {
  case 'show_rdv':
    CAppUI::js("window.parent.document.location.href = '?m=cabinet&tab=edit_planning&consultation_id={$object_id}';");
//    CAppUI::js("window.ExObject.onAfterSave = function() { window.document.location.href = '?m=cabinet&tab=edit_planning&consultation_id={$object_id}' };");
    break;

  default:
}

echo CExClassEvent::getJStrigger($ex_class_events);