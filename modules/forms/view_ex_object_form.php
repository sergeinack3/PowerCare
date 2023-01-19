<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassEvent;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassHostField;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkRead();

$ex_class_id           = CView::get("ex_class_id", "num");
$ex_object_id          = CView::get("ex_object_id", "num");
$object_guid           = CView::get("object_guid", "str");
$_element_id           = CView::get("_element_id", "str");
$event_name            = CView::get("event_name", "str");
$parent_view           = CView::get("parent_view", "str");
$readonly              = CView::get("readonly", "bool");
$print                 = CView::get("print", "bool");
$autoprint             = CView::get("autoprint", "bool");
$only_filled           = CView::get("only_filled", "bool");
$noheader              = CView::get("noheader", "bool");
$preview               = CView::get("preview", "bool");
$form_name             = CView::get("form_name", "str");
$memo                  = CView::get("memo", "str");
$quick_access_creation = CView::get("quick_access_creation", "str");

CView::checkin();

if (!$ex_class_id) {
    $msg = "Impossible d'afficher le formulaire sans connaître la classe de base";
    CAppUI::stepAjax($msg, UI_MSG_WARNING);
    trigger_error($msg, E_USER_ERROR);

    return;
}

// searching for a CExClassEvent
//$ex_class_event = new CExClassEvent;
//$ex_class_event->host_class =

$object = CMbObject::loadFromGuid($object_guid);

if ($object_guid && !$object) {
    // Object potentially merged and not replaced in CExObject references
    $object = CExObject::repairReferences($ex_class_id, $ex_object_id, $object_guid);

    if ($object === false) {
        CAppUI::stepAjax('Objet supprimé', UI_MSG_ERROR);
    }
}

if ($object->_id) {
    $object->loadComplete();

    if (CExClass::inHermeticMode(true)) {
        $object->needsRead();
    }
}

// searching for a CExClassEvent
$ex_class_event             = new CExClassEvent();
$ex_class_event->host_class = $object->_class;
if ($event_name) {
    $ex_class_event->event_name = $event_name;
}
$ex_class_event->ex_class_id = $ex_class_id;
$ex_class_event->loadMatchingObject();

/** @var CExObject $ex_object */

if (!$ex_object_id) {
    $ex_class = new CExClass();
    $ex_class->load($ex_class_id);

    if (CExClass::inHermeticMode(true)) {
        $ex_class->needsRead();
    }

    $ex_objects = $ex_class_event->getExObjectForHostObject($object, false);

    $ex_object = reset($ex_objects);

    if (!$ex_object) {
        $ex_object = $ex_class->getExObjectInstance();
    }
} else {
    $ex_object = new CExObject($ex_class_id);

    if (CExClass::inHermeticMode(true)) {
        $ex_object->needsRead();
    }
}

if ($preview) {
    $ex_object->_preview = true;
}

$printer_id = null;
$printers   = CMediusers::get()->loadRefFunction()->loadBackRefs("printers");
if (is_countable($printers) && count($printers)) {
    $printer    = reset($printers);
    $printer_id = $printer->_id;
}

$ex_object->_event_name = $event_name;

// Layout grid
if ($ex_object->_ref_ex_class->pixel_positionning && !$only_filled) {
    $grid        = null;
    $out_of_grid = null;
    $groups      = $ex_object->_ref_ex_class->getPixelGrid();
} else {
    [$grid, $out_of_grid, $groups] = $ex_object->_ref_ex_class->getGrid();
}

if ($ex_object_id || $ex_object->_id) {
    $ex_object->load($ex_object_id);
} else {
    $group_id = CGroups::loadCurrent()->_id;
    if ($object instanceof IGroupRelated && ($group = $object->loadRelGroup()) && $group->_id) {
        $group_id = $group->_id;
    }

    $ex_object->group_id = $group_id;
}

$creation_date = $ex_object->getCreateDate();

if ($object->_id && $object instanceof CSejour) {
    $object->loadRefCurrAffectation($creation_date)->updateView();
}

if (!$preview) {
    if ($readonly) {
        if (!$ex_object->canPerm("v")) {
            CAppUI::accessDenied();
        }
    } else {
        if (!$ex_object->canPerm($ex_object->_id ? "e" : "c")) {
            CAppUI::accessDenied();
        }
    }
}

// Host and reference objects
$ex_object->setObject($object);

if (!$ex_object->_id) {
    if (!$ex_object->reference_id && !$ex_object->reference_class) {
        $reference = $ex_class_event->resolveReferenceObject($object, 1);
        $ex_object->setReferenceObject_1($reference);
    }

    if (!$ex_object->reference2_id && !$ex_object->reference2_class) {
        $reference = $ex_class_event->resolveReferenceObject($object, 2);
        $ex_object->setReferenceObject_2($reference);
    }
} else {
    $ex_object->loadRefsLinks();
}

$ex_object->loadPictures();
$ex_object->loadRefGroup();

// loadAllFwdRefs ne marche pas bien (a cause de la clé primaire)
foreach ($ex_object->_specs as $_field => $_spec) {
    if ($_spec instanceof CRefSpec && $_field != $ex_object->_spec->key) {
        $class = $_spec->meta ? $ex_object->{$_spec->meta} : $_spec->class;

        if (!$class) {
            continue;
        }

        /** @var CMbObject $obj */
        $obj = new $class;
        $obj->load($ex_object->$_field);
        $ex_object->_fwd[$_field] = $obj;
    }
}

$all_fields = $ex_object->getReportedValues();
$ex_object->setFieldsDisplay($all_fields);
$ex_object->loadRefAdditionalObject();
$ex_object->_quick_access_creation = $quick_access_creation;

$ex_object->checkAutoIncrements();

// depends on setReferenceObject_1 and setReferenceObject_2
$ex_object->loadNativeViews($ex_class_event);

/** @var CExClassField[] $fields */
$fields = [];
foreach ($groups as $_group) {
    $fields = array_merge($_group->_ref_fields, $fields);

    if ($print) {
        $_group->getRankedItems();

        foreach ($_group->_ranked_items as $_ranked_item) {
            if (!$_ranked_item instanceof CExClassHostField) {
                continue;
            }

            $_ranked_item->getHostObject($ex_object);
        }
    }
}

CStoredObject::massLoadFwdRef($fields, "concept_id");
CStoredObject::massCountBackRefs($fields, "ex_triggers");

foreach ($fields as $_field) {
    $_field->loadTriggeredData();
}

$ex_object->_rel_patient = null;
if (in_array(IPatientRelated::class, class_implements($ex_object->object_class))) {
    if ($ex_object->_ref_object->_id) {
        $rel_patient = $ex_object->_ref_object->loadRelPatient();
        $rel_patient->loadIPP();
    } else {
        $rel_patient = new CPatient();

        if ($preview) {
            $rel_patient->_view            = "Patient exemple";
            $rel_patient->_IPP             = "0123456";
            $ex_object->_ref_object->_view = CAppUI::tr($ex_object->_ref_object->_class) . " test";
        }
    }

    $ex_object->_rel_patient = $rel_patient;
}

$can_delete = false;

if ($ex_object->_id) {
    $can_delete = ($ex_object->owner_id == CUser::get()->_id);
}

$can_delete = $can_delete || CModule::getInstalled("forms")->canAdmin();

/** @var CExConcept[] $concepts */
$concepts = CStoredObject::massLoadFwdRef($fields, "concept_id");
$lists    = CStoredObject::massLoadFwdRef($concepts, "ex_list_id");
CStoredObject::massLoadBackRefs($lists, "list_items");
CStoredObject::massLoadBackRefs($concepts, "list_items");

$formula_token_values = [];
foreach ($fields as $_field) {
    if ($_field->disabled) {
        continue;
    }

    $formula_token_values[$_field->name] = [
        "values"      => $_field->getFormulaValues(),
        "formula"     => $_field->formula,
        "formulaView" => "$_field->_formula",
        "low"         => $_field->result_threshold_low,
        "high"        => $_field->result_threshold_high,
    ];
}

foreach ($groups as $_group) {
    $_group->loadRefHostObjects($ex_object);
}

// Load IPP and NDA
$ref_objects = [
    $ex_object->_ref_object,
    $ex_object->_ref_reference_object_1,
    $ex_object->_ref_reference_object_2,
];

foreach ($ref_objects as $_object) {
    if ($_object instanceof CPatient) {
        $_object->loadIPP();
        continue;
    }

    if ($_object instanceof CSejour) {
        $_object->loadNDA();
        $_object->loadRefCurrAffectation($creation_date)->updateView();
        continue;
    }
}

$ex_object->isVerified();
CExObject::checkLocales();

$smarty = new CSmartyDP();
$smarty->assign("ex_object", $ex_object);
$smarty->assign("ex_object_id", $ex_object_id);
$smarty->assign("ex_class_id", $ex_class_id);
$smarty->assign("object_guid", $object_guid);
$smarty->assign("object", $object);
$smarty->assign("_element_id", $_element_id);
$smarty->assign("event_name", $event_name);
$smarty->assign("grid", $grid);
$smarty->assign("out_of_grid", $out_of_grid);
$smarty->assign("groups", $groups);
$smarty->assign("formula_token_values", $formula_token_values);
$smarty->assign("can_delete", $can_delete);
$smarty->assign("parent_view", $parent_view);
$smarty->assign("preview_mode", $preview);
$smarty->assign("ui_msg", CAppUI::getMsg());
$smarty->assign("ex_class_event", $ex_class_event);

$smarty->assign("readonly", $readonly);
$smarty->assign("print", $print);
$smarty->assign("autoprint", $autoprint);
$smarty->assign("only_filled", $only_filled);
$smarty->assign("noheader", $noheader);
$smarty->assign("form_name", $form_name);
$smarty->assign("printer_id", $printer_id);
$smarty->assign("memo", $memo);
$smarty->display("view_ex_object_form.tpl");
