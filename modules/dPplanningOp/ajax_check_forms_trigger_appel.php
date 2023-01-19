<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();
$object_guid = CView::get('object_guid', 'str notNull');
$event_name  = CView::get('event_name', 'str notNull');
CView::checkin();

$object = CStoredObject::loadFromGuid($object_guid);

$ex_class_events = CExClassEvent::getForObject($object, $event_name, "required");

if (!$object || !$object->_id) {
  CAppUI::commonError();
}

if ($ex_class_events) {
  echo CExClassEvent::getJStrigger($ex_class_events);
}
else {
  CAppUI::callbackAjax("Appel.edit", $object->_id);
}
