<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Services\PatientMergeService;
use Ox\Mediboard\PlanningOp\CSejour;

$objects_class  = CValue::getOrSession('objects_class');
$readonly_class = CValue::get('readonly_class');
$objects_id     = CValue::get('objects_id');
$mode           = CValue::get('mode');

if (!is_array($objects_id)) {
    $objects_id = explode("-", $objects_id ?? '');
}

$user = CMediusers::get();

CMbArray::removeValue("", $objects_id);

$objects    = [];
$result     = null;
$checkMerge = null;
$statuses   = [];
$blockMerge = true;

$merge_type = null;
$warnings   = [];

if (class_exists($objects_class ?? '') && count($objects_id)) {
    foreach ($objects_id as $object_id) {
        /** @var CMbObject $object */
        $object     = new $objects_class;
        $merge_type = $object->_spec->merge_type;
        if ($merge_type == 'none') {
            CAppUI::stepAjax("Merging_%sclass_is_forbidden_by_spec", UI_MSG_ERROR, CAppUI::tr($object->_class));
        }

        // the CMbObject is loaded
        if (!$object->load($object_id)) {
            CAppUI::setMsg("Chargement impossible de l'objet [$object_id]", UI_MSG_ERROR);
            continue;
        }

        $object->loadView();
        $object->loadAllFwdRefs();

        $object->_selected = false;
        $object->_disabled = false;

        $objects[] = $object;
    }

    // Default préselection of first object
    $_selected = reset($objects);

    // selection of the first CSejour or CPatient with an ext ID
    if ($objects_class == "CSejour" || $objects_class == "CPatient") {
        $no_extid = [];
        $extid    = [];

        foreach ($objects as $_object) {
            if ($_object instanceof CSejour && $_object->_NDA
                || $_object instanceof CPatient && $_object->_IPP
            ) {
                $extid[] = $_object;
            } else {
                $no_extid[] = $_object;
            }
        }

        if (count($no_extid) < count($objects)) {
            // Selection disabled for idex less objects
            if (CAppUI::conf("merge_prevent_base_without_idex") == 1) {
                foreach ($no_extid as $_object) {
                    $_object->_disabled = true;
                }

                $_selected = reset($extid);
            }
        }
    }

    // Selected object IS selected (!)
    $_selected->_selected = true;

    // Check merge
    /** @var CMbObject $result */
    $result = new $objects_class;

    try {
        $result->checkMerge($objects);
        $checkMerge = null;
    } catch (Throwable $t) {
        $checkMerge = $t->getMessage();
    }

    if ($objects_class === 'CPatient') {
        $patient_merge_service = new PatientMergeService($objects);
        $warnings = $patient_merge_service->getWarnings();
    }

    // Merge trivial fields
    foreach (array_keys($result->getPlainFields()) as $field) {
        $values = CMbArray::pluck($objects, $field);
        CMbArray::removeValue("", $values);

        // No values
        if (!count($values)) {
            $statuses[$field] = "none";
            continue;
        }

        $result->$field = reset($values);

        // One unique value
        if (count($values) == 1) {
            $statuses[$field] = "unique";
            continue;
        }

        // Multiple values
        $statuses[$field] = count(array_unique($values)) == 1 ? "duplicate" : "multiple";
    }

    $result->updateFormFields();
    $result->loadAllFwdRefs();
}

// Count statuses
$counts = [
    "none"      => 0,
    "unique"    => 0,
    "duplicate" => 0,
    "multiple"  => 0,
];

foreach ($statuses as $status) {
    $counts[$status]++;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("objects", $objects);
$smarty->assign("objects_class", $objects_class);
$smarty->assign("objects_id", $objects_id);
$smarty->assign("merge_type", $merge_type);
$smarty->assign("result", $result);
$smarty->assign("statuses", $statuses);
$smarty->assign("user", $user);
$smarty->assign("counts", $counts);
$smarty->assign("checkMerge", $checkMerge);
$smarty->assign("mode", $mode);
$smarty->assign("readonly_class", $readonly_class);
$smarty->assign("warnings", $warnings);

$smarty->display("object_merger.tpl");
