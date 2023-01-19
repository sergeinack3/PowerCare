<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassCategory;
use Ox\Mediboard\System\Forms\CExClassEvent;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassHostField;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExLink;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkRead();

$reference_class        = CValue::get("reference_class");
$reference_id           = CValue::get("reference_id");
$cross_context_class    = CValue::get("cross_context_class");
$cross_context_id       = CValue::get("cross_context_id");
$creation_context_class = CValue::get("creation_context_class");
$creation_context_id    = CValue::get("creation_context_id");
$event_names            = CValue::get('event_names');

$detail          = CValue::get("detail", 1);
$ex_class_id     = CValue::get("ex_class_id");
$target_element  = CValue::get("target_element");
$other_container = CValue::get("other_container");
$print           = CValue::get("print");
$start           = CValue::get("start", 0);
$limit           = CValue::get("limit");
$only_host       = CValue::get("only_host");
$readonly        = CValue::get("readonly");
$can_search      = CValue::get("can_search");
$keep_session    = CValue::get("keep_session");
$date_time_min   = CValue::get("date_time_min");
$date_time_max   = CValue::get("date_time_max");

// Search mode
$search_mode    = CValue::get("search_mode", 0);
$date_min       = CValue::get("date_min");
$date_max       = CValue::get("date_max");
$group_id       = CValue::get("group_id");
$concept_search = CValue::get("concept_search");

$owner_id = CValue::get("owner_id");

CValue::setSession('reference_class', $reference_class);
CValue::setSession('reference_id', $reference_id);

if (!$keep_session) {
    CSessionHandler::writeClose();
}

if ($search_mode) {
    CView::enforceSlave();
}

if ($reference_class) {
    /** @var CMbObject $reference */
    $reference = new $reference_class;

    if ($reference_id) {
        $reference->load($reference_id);
    }
} else {
    $reference = null;
}

CExClassField::$_load_lite = true;
CExObject::$_multiple_load = true;
CExObject::$_load_lite     = $detail < 2;

$group_id = ($group_id ? $group_id : CGroups::loadCurrent()->_id);
$where    = [
    "group_id = '$group_id' OR group_id IS NULL",
];

if ($ex_class_id) {
    $where['ex_class_id'] = "= '$ex_class_id'";
}

if (empty(CExClass::$_list_cache)) {
    $ex_class = new CExClass();

    /** @var CExClass[] $ex_classes */
    $ex_classes = $ex_class->loadList($where, "name");

    $categories = CStoredObject::massLoadFwdRef($ex_classes, "category_id");
    $categories = CStoredObject::naturalSort($categories, ["title"]);

    $categories = [new CExClassCategory()] + $categories;

    foreach ($ex_classes as $_ex_class) {
        $_category_id                                                = $_ex_class->category_id ?: 0;
        $categories[$_category_id]->_ref_ex_classes[$_ex_class->_id] = $_ex_class;
    }

    if (!CExObject::$_locales_cache_enabled && $detail > 1) {
        foreach ($ex_classes as $_ex_class) {
            foreach ($_ex_class->loadRefsGroups() as $_group) {
                $_group->loadRefsFields();

                foreach ($_group->_ref_fields as $_field) {
                    $_field->updateTranslation();
                }
            }
        }
    }

    CExClass::$_list_cache         = $ex_classes;
    CExClassCategory::$_list_cache = $categories;
}

/** @var CExObject[][] $ex_objects */
$ex_objects = [];

$ex_classes          = [];
$ex_objects_counts   = [];
$ex_objects_results  = [];
$ex_classes_creation = [];

if (!$limit) {
    if ($print) {
        $limit = 5;
    } else {
        switch ($detail) {
            case 3:
            case 2:
                $limit = ($search_mode ? 50 : ($ex_class_id ? 20 : 10));
                break;

            case 1:
                $limit = ($ex_class_id ? 50 : 25);
                break;

            default:
            case 0:
        }
    }
}

$step  = $limit;
$total = 0;

if ($limit) {
    $limit = "$start, $limit";
}

$ref_objects_cache = [];

$search = null;
if ($concept_search) {
    $concept_search = stripslashes($concept_search);
    $search         = CExConcept::parseSearch($concept_search);
}

$ex_class_event  = new CExClassEvent();
$ex_class_events = null;

$ex_link = new CExLink();
$ds      = $ex_link->getDS();

$group_id = ($group_id ? $group_id : CGroups::loadCurrent()->_id);
$where    = [
    "ex_link.group_id" => "= '$group_id'",
];

if ($owner_id) {
    $where["ex_link.owner_id"] = $ds->prepare('= ?', $owner_id);
}

if ($ex_class_id) {
    $where['ex_link.ex_class_id'] = "= '$ex_class_id'";
}

$ljoin = [];

$creation_context = null;

if (!$creation_context_class || !$creation_context_id) {
    $creation_context_class = $reference_class;
    $creation_context_id    = $reference_id;
}

if ($creation_context_class) {
    /** @var CSejour|CPatient|CConsultation $creation_context */
    $creation_context = new $creation_context_class;
    $creation_context->load($creation_context_id);
}

if ($search_mode) {
    if ($reference_class && $reference_id) {
        $ds                            = $ex_class_event->getDS();
        $where["ex_link.object_class"] = $ds->prepare("=?", $reference_class);
        $where["ex_link.object_id"]    = $ds->prepare("=?", $reference_id);
    } else {
        $where["ex_link.level"] = "= 'object'";
    }

    $where["ex_link.datetime_create"] = "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'";

    if ($ex_class_id) {
        $where["ex_link.ex_class_id"] = "= '$ex_class_id'";
    }

    if (!empty($search)) {
        $_ex_class = new CExClass();
        $_ex_class->load($ex_class_id);

        $ljoin["ex_object_$ex_class_id"] = "ex_object_$ex_class_id.ex_object_id = ex_link.ex_object_id";

        $where = array_merge($where, $_ex_class->getWhereConceptSearch($search));
    }
} else {
    if ($date_time_min && $date_time_max) {
        $where["ex_link.datetime_create"] = "BETWEEN '$date_time_min' AND '$date_time_max'";
    } elseif ($date_time_min) {
        $where[] = "ex_link.datetime_create >= '" . $date_time_min . "'";
    } elseif($date_time_max) {
        $where[] = "ex_link.datetime_create <= '" . $date_time_max. "'";
    }

    if ($cross_context_class && $cross_context_id) {
        $where["ex_link.object_class"] = "= '$cross_context_class'";
        $where["ex_link.object_id"]    = "= '$cross_context_id'";

        $where["ex_class.cross_context_class"] = "= '$cross_context_class'";
    } else {
        $where["ex_link.object_class"] = "= '$reference_class'";
        $where["ex_link.object_id"]    = "= '$reference_id'";
    }

    if ($only_host) {
        $where["ex_link.level"] = " = 'object'";
    }
}

$alerts = [];
if ($reference && $reference->_id) {
    $alerts = CExObject::getThresholdAlerts([$reference], $ex_class_id);
}

$order = "ex_class.name ASC, ex_link.ex_object_id DESC";

$ljoin["ex_class"] = "ex_class.ex_class_id = ex_link.ex_class_id";

$fields = [
    "ex_link.ex_class_id",
    "ex_link.ex_object_id",
];
$counts = $ex_link->countMultipleList($where, $order, "ex_class_id", $ljoin, $fields);

foreach ($counts as $_count) {
    $_total       = $_count["total"];
    $_ex_class_id = $_count["ex_class_id"];

    // CExObject from another group (group_id changed from the CExClass)
    if (!array_key_exists($_ex_class_id, CExClass::$_list_cache)) {
        $_ex_class_missing = new CExClass();
        if ($_ex_class_missing->load($_ex_class_id)) {
            CExClass::$_list_cache[$_ex_class_id]                             = $_ex_class_missing;
            CExClassCategory::$_list_cache[0]->_ref_ex_classes[$_ex_class_id] = $_ex_class_missing;
        } else {
            continue;
        }
    }

    $_ex_class = CExClass::$_list_cache[$_ex_class_id];

    // Counts
    $total                            = max($_total, $total);
    $ex_objects_counts[$_ex_class_id] = $_total;

    // Formula results
    $ex_objects_results[$_ex_class_id] = null;
    if ($_ex_class->_formula_field && !$search_mode) {
        $where_formula = $where;
        unset($where_formula["ex_class.cross_context_class"]);
        $ex_objects_results[$_ex_class_id] = $_ex_class->getFormulaResult($_ex_class->_formula_field, $where_formula);
    }

    if ($detail < 1) {
        continue;
    }

    /** @var CExLink[] $links */
    $where["ex_link.ex_class_id"] = "= '$_ex_class_id'";
    $links                        = $ex_link->loadList($where, $order, $limit, "ex_link.ex_object_id", $ljoin);

    CExLink::massLoadExObjects($links);

    /** @var CExObject[] $_ex_objects */
    $_ex_objects = [];
    foreach ($links as $_link) {
        $_ex               = $_link->loadRefExObject();
        $_ex->_ex_class_id = $_link->ex_class_id;
        $_ex->load();

        $_ex_objects[$_link->ex_object_id] = $_ex;
    }

    /** @var CExObject $_ex */
    foreach ($_ex_objects as $_ex) {
        if (!$_ex->_id) {
            continue;
        }

        $_ex->updateCreationFields();

        $guid = "$_ex->object_class-$_ex->object_id";

        if (!isset($ref_objects_cache[$guid])) {
            $_ex->loadTargetObject();

            if ($detail < 2) {
                $_ex->loadComplete(); // to get the view
            }

            $ref_objects_cache[$guid] = $_ex->_ref_object;
        } else {
            $_ex->_ref_object = $ref_objects_cache[$guid];
        }

        if ($_ex->additional_id) {
            $_ex->loadRefAdditionalObject();
        }

        $ex_objects[$_ex_class_id][$_ex->_id] = $_ex;
    }

    if (isset($ex_objects[$_ex_class_id])) {
        CExObject::checkVerified($ex_objects[$_ex_class_id]);

        krsort($ex_objects[$_ex_class_id]);
    }
}

// Repair for references for current object
foreach ($ex_objects as $_ex_class_id => $_ex_objects) {
    foreach ($_ex_objects as $_ex_object_id => $_ex_object) {
        CExObject::repairReferences($_ex_class_id, $_ex_object_id, $_ex_object->_ref_object->_guid);
    }
}

// Can create new
if ($detail <= 0.5 || $detail == 2) {
    // Loading the events
    if ($ex_class_events === null) {
        $_ex_class_creation = [];
        $ex_class_events    = [];

        foreach (CExClass::$_list_cache as $_ex_class_id => $_ex_class) {
            if ($_ex_class->group_id && $_ex_class->group_id != $group_id) {
                continue;
            }

            if (!isset($ex_objects_counts[$_ex_class_id])) {
                continue;
            }

            if (!$_ex_class->canPerm("c")) {
                continue;
            }

            if (!$_ex_class->conditional && (!$cross_context_class || $cross_context_class == $_ex_class->cross_context_class)) {
                $_ex_class_creation[] = $_ex_class_id;
            }
        }

        $where = [
            "ex_class_event.ex_class_id" => $ex_class_event->getDS()->prepareIn($_ex_class_creation),
            "ex_class_event.disabled"    => "= '0'",
        ];

        /** @var CExClassEvent[] $_ex_class_events */
        $_ex_class_events = $ex_class_event->loadList($where);

        CStoredObject::massLoadBackRefs($_ex_class_events, "constraints");
    }

    if ($creation_context) {
        foreach ($_ex_class_events as $_id => $_ex_class_event) {
            if ($creation_context->_class !== $_ex_class_event->host_class
                || !$_ex_class_event->checkConstraints($creation_context)
            ) {
                continue;
            }

            $ex_classes_creation[$_ex_class_event->ex_class_id][$_ex_class_event->_id] = $_ex_class_event;
        }
    }
}

$formula_token_values = [];
if ($detail == 2) {
    foreach ($ex_objects as $_ex_class_id => $_ex_objects) {
        /** @var CExObject $first */
        $first = reset($_ex_objects);

        if (!$first) {
            continue;
        }

        $_ex_class = $first->_ref_ex_class;

        // Add an empty CExObject at the beginning
        if ($_ex_class->canPerm("c") && !$search_mode && $_ex_class->allow_create_in_column) {
            /** @var CExClassEvent $ex_class_event */
            $ex_class_event = (isset($ex_classes_creation[$_ex_class_id])) ? reset(
                $ex_classes_creation[$_ex_class_id]
            ) : null;

            if (!$ex_class_event || !$ex_class_event->checkConstraints($creation_context)) {
                continue;
            }

            $_new_ex_object = new CExObject($_ex_class_id);
            $_new_ex_object->setObject($reference);
            $_new_ex_object->group_id = $group_id;

            $reference_1 = $ex_class_event->resolveReferenceObject($reference, 1);
            $_new_ex_object->setReferenceObject_1($reference_1);

            $reference_2 = $ex_class_event->resolveReferenceObject($reference, 2);
            $_new_ex_object->setReferenceObject_2($reference_2);

            $all_fields = $_new_ex_object->getReportedValues();
            $_new_ex_object->setFieldsDisplay($all_fields);

            array_unshift($ex_objects[$_ex_class_id], $_new_ex_object);
            $_ex_objects = $ex_objects[$_ex_class_id];


            foreach ($all_fields as $_field) {
                $formula_token_values[$_field->name] = [
                    "values"      => $_field->getFormulaValues(),
                    "formula"     => $_field->formula,
                    "formulaView" => $_field->_formula,
                    "low"         => $_field->result_threshold_low,
                    "high"        => $_field->result_threshold_high,
                ];
            }
        }

        // Colors WIP
        //foreach ($_ex_objects as $_ex_object) {
        //  $_ex_object->getFieldsStyle();
        //}

        foreach ($_ex_class->_ref_groups as $_ex_group) {
            $_ex_group->_empty = true;

            $_ex_group->getRankedItems();
            foreach ($_ex_group->_ranked_items as $_ranked_item) {
                if (!$_ranked_item instanceof CExClassHostField) {
                    continue;
                }

                foreach ($_ex_objects as $_ex_object) {
                    $_ex_object->_ref_host_fields[$_ranked_item->field] = $_ranked_item->getHostObject($_ex_object);
                }
            }

            foreach ($_ex_group->_ref_fields as $_ex_field) {
                $_ex_field->_empty = true;

                if ($_ex_field->hidden) {
                    continue;
                }


                // Colors WIP
                //$_ex_field->getDefaultProperties();

                foreach ($_ex_objects as $_ex_object) {
                    if ($_ex_object->{$_ex_field->name} != "") {
                        $_ex_field->_empty = false;
                        $_ex_group->_empty = false;
                        break;
                    }
                }
            }

            // Colors WIP
            //foreach ($_ex_group->_ref_messages as $_ex_message) {
            //  $_ex_message->getDefaultProperties();
            //}
        }
    }
}

if ($detail == 3) {
    foreach ($ex_objects as $_ex_class_id => $_ex_objects) {
        /** @var CExObject $first */
        $first = reset($_ex_objects);

        if (!$first) {
            continue;
        }

        $_ex_class = $first->_ref_ex_class;

        foreach ($_ex_class->_ref_groups as $_ex_group) {
            $_ex_group->getRankedItems();

            foreach ($_ex_group->_ranked_items as $_ranked_item) {
                if (!$_ranked_item instanceof CExClassHostField) {
                    continue;
                }

                foreach ($_ex_objects as $_ex_object) {
                    $_ex_object->_ref_host_fields[$_ranked_item->field] = $_ranked_item->getHostObject($_ex_object);
                }
            }
        }
    }
}

ksort($ex_objects);

if ($print) {
    // Remove no allowed forms
    foreach ($ex_objects as $_id => &$_ex_objects) {
        $_ex_objects = array_filter(
            $_ex_objects, function ($_ex_oject) {
            return $_ex_oject->canPerm('v');
        }
        );

        // If no ex_objects remaining remove the title
        if (!$_ex_objects) {
            unset($ex_objects[$_id]);
        }
    }
}

CExObject::checkLocales();

// Création du template
$smarty = new CSmartyDP("modules/forms");

if (CModule::getActive("appFineClient") && ($detail == 0 || $detail == 0.5)) {
    $orders_item_form = $orders_item_form_waiting = [];
    $reference        = CMbObject::loadFromGuid("$reference->_class-$reference->_id");

    if ($reference instanceof CSejour || $reference instanceof CConsultation) {
        $orders_item_form = $reference->loadRefsOrdersItem(
            [
                "received_datetime" => "IS NULL",
                "canceled"          => " = '0' ",
                "context_class"     => " = '$reference->_class' ",
                "context_id"        => " = '$reference->_id' ",
            ]
        );

        foreach ($orders_item_form as $_order_form) {
            $order = $_order_form->loadRefOrder();

            if ($order->context_class !== 'CExClass') {
                continue;
            }

            $orders_item_form_waiting[] = $_order_form;
        }
    }
    $smarty->assign("orders_item_form", $orders_item_form_waiting);
}

$smarty->assign("reference_class", $reference_class);
$smarty->assign("reference_id", $reference_id);
$smarty->assign("cross_context_class", $cross_context_class);
$smarty->assign("cross_context_id", $cross_context_id);
$smarty->assign("creation_context", $creation_context);
$smarty->assign("event_names", $event_names);
$smarty->assign("reference", $reference);
$smarty->assign("ex_objects", $ex_objects);
$smarty->assign("ex_objects_counts", $ex_objects_counts);
$smarty->assign("ex_objects_results", $ex_objects_results);
$smarty->assign("limit", $limit);
$smarty->assign("step", $step);
$smarty->assign("total", $total);
$smarty->assign("ex_classes_creation", $ex_classes_creation);
$smarty->assign("ex_classes", CExClass::$_list_cache);
$smarty->assign("ex_class_categories", CExClassCategory::$_list_cache);
$smarty->assign("detail", $detail);
$smarty->assign("ex_class_id", $ex_class_id);
$smarty->assign("target_element", $target_element);
$smarty->assign("other_container", $other_container);
$smarty->assign("print", $print);
$smarty->assign("start", $start);
$smarty->assign("search_mode", $search_mode);
$smarty->assign("readonly", $readonly);
$smarty->assign("can_search", $can_search);
$smarty->assign("formula_token_values", $formula_token_values);
$smarty->assign("alerts", $alerts);
$smarty->display("inc_list_ex_object.tpl");
