<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//CCanDo::checkAdmin(); // Don't check permissions

use Ox\Core\CApp;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClassConstraint;
use Ox\Mediboard\System\Forms\CExClassEvent;

$object_guid = CView::get("object_guid", "str");
$event_name  = CView::get("event_name", "str");

CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

CExClassConstraint::$_load_lite = true;

$ex_class_event = new CExClassEvent();
$ds             = $ex_class_event->_spec->ds;
$group_id       = CGroups::loadCurrent()->_id;

$where = array(
  "ex_class_event.host_class" => $ds->prepare("=%", $object->_class),
  "ex_class_event.event_name" => $ds->prepare("=%", $event_name),
  "ex_class_event.disabled"   => $ds->prepare("=%", 0),
  "ex_class.conditional"      => $ds->prepare("=%", 0),
  $ds->prepare("ex_class.group_id = % OR ex_class.group_id IS NULL", $group_id),
);

$ljoin = array(
  "ex_class" => "ex_class.ex_class_id = ex_class_event.ex_class_id",
);

/** @var CExClassEvent[] $ex_class_events */
$ex_class_events = $ex_class_event->loadList($where, null, null, null, $ljoin);

CStoredObject::massLoadBackRefs($ex_class_events, "constraints");

$ex_class_events_struct = array();

foreach ($ex_class_events as $_ex_class_event) {
  if ($_ex_class_event->checkConstraints($object)) {
    $ex_class_events_struct[] = array(
      "ex_class_event_id" => $_ex_class_event->_id,
      "ex_class_id"       => $_ex_class_event->ex_class_id,
      "event_name"        => $event_name,
      "object_guid"       => $object_guid,
    );
  }
}

CApp::json($ex_class_events_struct);
