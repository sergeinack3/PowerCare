<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//CCanDo::checkAdmin();

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClassEvent;
use Ox\Mediboard\System\Forms\CExObject;

$object_guid = CView::get("object_guid", "str");
$event_name  = CView::get("event_name", "str");
$_element_id = CView::get("_element_id", "str");
$form_name   = CView::get("form_name", "str");

CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

//CExObject::$_load_lite = true;

$ex_class_event = new CExClassEvent();
$ds             = $ex_class_event->getDS();
$group_id       = CGroups::loadCurrent()->_id;

$where = array(
  "ex_class_event.host_class" => $ds->prepare("=%", $object->_class),
  "ex_class_event.event_name" => $ds->prepare("=%", $event_name),
  //"ex_class_event.disabled"    => $ds->prepare("=%", 0),
  "ex_class.conditional"      => $ds->prepare("=%", 0),
  $ds->prepare("ex_class.group_id = % OR ex_class.group_id IS NULL", $group_id),
);
$ljoin = array(
  "ex_class" => "ex_class.ex_class_id = ex_class_event.ex_class_id",
);

/** @var CExClassEvent[] $ex_class_events */
$ex_class_events = $ex_class_event->loadList($where, null, null, null, $ljoin);
$ex_classes      = array();
$ex_objects      = array();

$count           = 0;
$count_available = count($ex_class_events);
foreach ($ex_class_events as $_id => $_ex_class_event) {
  $_ex_class = $_ex_class_event->loadRefExClass();
  $_ex_class->getFormulaField();

  $ex_classes[$_ex_class->_id] = $_ex_class;

  if ($_ex_class_event->disabled || !$_ex_class_event->checkConstraints($object) || !$_ex_class->canPerm("c")) {
    $count_available--;
  }

  $_ex_objects = $_ex_class_event->getExObjectForHostObject($object);

  // Only keep first if in "pre fill" mode
  if ($form_name && count($_ex_objects)) {
    $_ex_objects = array(reset($_ex_objects));
  }

  foreach ($_ex_objects as $_ex_object) {
    $_ex_object->load(); // Needed
    $_ex_object->getCreateDate();
  }

  $count += count($_ex_objects);

  $ex_objects[$_ex_class->_id] = $_ex_objects;
}

foreach ($ex_objects as $_id => $_ex_object) {
  if (!count($_ex_object)) {
    unset($ex_objects[$_id]);
  }
}

// Complétude des formulaires pour la préparation entrée avant validation
if ($object->_class == 'CSejour' || $object->_class == 'COperation') {
  /** @var CMbObject $object */
  $object->getColorCompletenessLastForm($event_name);

  if (!CAppUI::conf("forms CExClass show_color_score_form")
    && ($event_name == "preparation_entree" || $event_name == "sortie_preparee")
  ) {
    $object->_completeness_color_form = "grey";
  }
}

CExObject::checkLocales();

$smarty = new CSmartyDP();
$smarty->assign("ex_classes", $ex_classes);
$smarty->assign("ex_objects", $ex_objects);
$smarty->assign("object", $object);
$smarty->assign("event_name", $event_name);
$smarty->assign("count", $count);
$smarty->assign("count_available", $count_available);
$smarty->assign("_element_id", $_element_id);
$smarty->assign("form_name", $form_name);
$smarty->display("inc_widget_ex_classes_new.tpl");
