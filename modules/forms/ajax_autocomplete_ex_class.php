<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassCategory;
use Ox\Mediboard\System\Forms\CExClassEvent;

CCanDo::checkRead();

$event_names            = CView::get('event_names', 'str');
$keywords               = CView::get("keywords", "str");
$reference_class        = CView::get("reference_class", "str");
$reference_id           = CView::get("reference_id", "ref class|CMbObject meta|reference_class");
$cross_context_class    = CView::get("cross_context_class", "str");
$cross_context_id       = CView::get("cross_context_id", "ref class|CMbObject meta|cross_context_class");
$creation_context_class = CView::get("creation_context_class", "str");
$creation_context_id    = CView::get("creation_context_id", "ref class|CMbObject meta|creation_context_class");

CView::checkin();

CView::enableSlave();

$group_id = CGroups::loadCurrent()->_id;

$where = array(
  "group_id = '$group_id' OR group_id IS NULL",
  "ex_class_event.disabled" => "= '0'",
  "ex_class.conditional"    => "= '0'",
);

$ljoin = array(
  "ex_class_event" => "ex_class_event.ex_class_id = ex_class.ex_class_id",
);

$ds = CSQLDataSource::get('std');

if ($event_names) {
  $event_names = explode('|', $event_names);

  $where['ex_class_event.event_name'] = $ds::prepareIn($event_names);
}

if ($cross_context_class) {
  $where["cross_context_class"] = "= '$cross_context_class'";
}

$ex_class = new CExClass();

/** @var CExClass[] $ex_classes */
$ex_classes = $ex_class->seek($keywords, $where, null, null, $ljoin, null, 'ex_class.ex_class_id');

if ($creation_context_class) {
  /** @var CSejour|CPatient|CConsultation $creation_context */
  $creation_context = new $creation_context_class;
  $creation_context->load($creation_context_id);
}

// Loading the events
$ex_classes_filtered = array();

foreach ($ex_classes as $_ex_class_id => $_ex_class) {
  if (!$_ex_class->canPerm("c")) {
    unset($ex_classes[$_ex_class_id]);
    continue;
  }

  if (!$cross_context_class || $cross_context_class === $_ex_class->cross_context_class) {
    $ex_classes_filtered[$_ex_class_id] = $_ex_class;
  }
}

/** @var CExClassEvent[] $ex_class_events */
$ex_class_events = CStoredObject::massLoadBackRefs($ex_classes_filtered, "events", null, array("disabled = '0'"));
CStoredObject::massLoadBackRefs($ex_class_events, "constraints");
CStoredObject::massLoadFwdRef($ex_class_events, "ex_class_id");

/** @var CExClassEvent[] $ex_classes_creation */
$ex_classes_creation = array();
$category_ids        = array();

foreach ($ex_class_events as $_id => $_ex_class_event) {
  $_classes = $_ex_class_event->getCreationClasses();

  if (!in_array($creation_context->_class, $_classes)) {
    continue;
  }

  $_ex_class_event->getQuickAccess($creation_context->_class);

  // Let checkConstraint AT THE BEGINNING
  if (($_ex_class_event->checkConstraints($creation_context)
      && $_ex_class_event->host_class === $creation_context->_class)
    || $_ex_class_event->_quick_access
  ) {
    $_ex_class = $_ex_class_event->loadRefExClass();

    // Un événement en quick_access et dont la contrainte est satisfaite est prioritaire sur le reste
    if ((!isset($ex_classes_creation[$_ex_class->_id]) && !$_ex_class_event->_quick_access)
      || ($_ex_class_event->_quick_access && $_ex_class_event->_ref_constraint_object && $_ex_class_event->_ref_constraint_object->_id)
    ) {
      $ex_classes_creation[$_ex_class->_id] = $_ex_class_event;
    }

    $_category_id                = $_ex_class->category_id ?: 0;
    $category_ids[$_category_id] = $_category_id;
  }
}

usort(
  $ex_classes_creation,
  function ($a, $b) {
    return strnatcasecmp($a->_ref_ex_class->name, $b->_ref_ex_class->name);
  }
);

$ex_class_category = new CExClassCategory();
$categories_db     = $ex_class_category->loadAll($category_ids);
$categories_db     = CStoredObject::naturalSort($categories_db, array("title"));

// Add an empty category at the beginning
$categories = array(0 => $ex_class_category);
foreach ($categories_db as $_category) {
  $categories[$_category->_id] = $_category;
}

foreach ($ex_classes_creation as $_ex_class_id => $_ex_class_event) {
  $_ex_class    = $_ex_class_event->loadRefExClass();
  $_category_id = $_ex_class->category_id ?: 0;

  $categories[$_category_id]->_ref_ex_classes[$_ex_class_id] = $_ex_class_event;
}

$smarty = new CSmartyDP();
$smarty->assign('categories', $categories);
$smarty->assign('reference_class', $reference_class);
$smarty->assign('reference_id', $reference_id);
$smarty->display('inc_ex_class_autocomplete.tpl');
