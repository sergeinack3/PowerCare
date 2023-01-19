<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassEvent;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExLink;
use Ox\Mediboard\System\Forms\CExObject;

if (CExClass::inHermeticMode(false)) {
    CCanDo::checkAdmin();
} else {
    CCanDo::checkEdit();
}

CStoredObject::$useObjectCache = false;
CApp::setMemoryLimit('1024M');
CApp::setTimeLimit(600);

$ex_class_id    = CView::get("ex_class_id", "num");
$concept_search = CView::get("concept_search", "str");
$date_min       = CView::get("date_min", "date");
$date_max       = CView::get("date_max", "date");
$group_id       = CView::get("group_id", "ref class|CGroups");

CView::checkin();
CView::enforceSlave();

$limit = 10000;

$group_id = ($group_id ? $group_id : CGroups::loadCurrent()->_id);
$where    = [
    "group_id = '$group_id' OR group_id IS NULL",
];

$ex_class = new CExClass();
$ex_class->load($ex_class_id);

foreach ($ex_class->loadRefsGroups() as $_group) {
    $_group->loadRefsFields();

    foreach ($_group->_ref_fields as $_field) {
        if ($_field->disabled) {
            continue;
        }

        $_field->updateTranslation();
    }
}

/** @var CExObject[] $ex_objects */
$ex_objects = [];

$ref_objects_cache = [];

$search = null;
if ($concept_search) {
    $concept_search = stripslashes($concept_search);
    $search         = CExConcept::parseSearch($concept_search);
}

$ex_class_event  = new CExClassEvent();
$ex_class_events = null;

$ex_link = new CExLink();

$where = [
    "ex_link.group_id"        => "= '$group_id'",
    "ex_link.ex_class_id"     => "= '$ex_class_id'",
    "ex_link.level"           => "= 'object'",
    "ex_link.datetime_create" => "BETWEEN '$date_min 00:00:00' AND '$date_max 23:59:59'",
];

$ljoin = [];

if (!empty($search)) {
    $ljoin["ex_object_$ex_class_id"] = "ex_object_$ex_class_id.ex_object_id = ex_link.ex_object_id";

    $where = array_merge($where, $ex_class->getWhereConceptSearch($search));
}

$order = "ex_class.name ASC, ex_link.ex_object_id DESC";

$ljoin["ex_class"] = "ex_class.ex_class_id = ex_link.ex_class_id";

/** @var CExLink[] $links */
$links = $ex_link->loadList($where, $order, $limit, "ex_link.ex_object_id", $ljoin);

//CExLink::massLoadExObjects($links);

/** @var CExObject[] $ex_objects */
foreach ($links as $_link) {
    $_ex               = $_link->loadRefExObject();
    $_ex->_ex_class_id = $_link->ex_class_id;
    $_ex->load();

    $_ex->updateCreationFields();

    $guid = "$_ex->object_class-$_ex->object_id";

    if (!isset($ref_objects_cache[$guid])) {
        $_ex->loadTargetObject();

        $ref_objects_cache[$guid] = $_ex->_ref_object;
    } else {
        $_ex->_ref_object = $ref_objects_cache[$guid];
    }

    if ($_ex->additional_id) {
        $_ex->loadRefAdditionalObject();
    }

    $ex_objects[$_ex->_id] = $_ex;
}

krsort($ex_objects);

$csv = new CCSVFile();

$get_field = function ($class, $field) {
    return CAppUI::tr($class) . " - " . CAppUI::tr("$class-$field");
};

$fields = [
    ["CPatient", "_IPP"],
    ["CPatient", "nom"],
    ["CPatient", "nom_jeune_fille"],
    ["CPatient", "prenom"],
    ["CPatient", "prenoms"],
    ["CPatient", "naissance"],
    ["CPatient", "sexe"],

    ["CSejour", "_NDA"],
    ["CSejour", "rques"],
    ["CSejour", "praticien_id"],
    ["CSejour", "type"],
    ["CSejour", "entree"],
    ["CSejour", "sortie"],
    ["CSejour", "annule"],
    ["CSejour", "DP"],
    ["CSejour", "DR"],

    ["COperation", "chir_id"],
    ["COperation", "anesth_id"],
    ["COperation", "salle_id"],
    ["COperation", "date"],
    ["COperation", "libelle"],
    ["COperation", "cote"],
    ["COperation", "temp_operation"],
    ["COperation", "codes_ccam"],
];

$meta_fields = [
    ["CExObject", "datetime_create"],
    ["CExObject", "datetime_edit"],
    ["CExObject", "owner_id"],
];

$row = [];
foreach ($fields as $_field) {
    $row[] = $get_field($_field[0], $_field[1]);
}
foreach ($meta_fields as $_field) {
    $row[] = $get_field($_field[0], $_field[1]);
}

foreach ($ex_class->loadRefsGroups() as $_group) {
    foreach ($_group->_ref_fields as $_field) {
        if ($_field->disabled) {
            continue;
        }

        $row[] = $_group->name . " - " . CAppUI::tr("CExObject_$ex_class->_id-$_field->name");
    }
}

// Write column headers
$csv->writeLine($row);

foreach ($ex_objects as $_ex_object) {
    /** @var CMbObject[] $_objects */
    $_objects = [];

    /** @var CPatient $_patient */
    $_patient = $_ex_object->getReferenceObject("CPatient");
    if ($_patient) {
        $_patient->loadIPP();
    }
    $_objects["CPatient"] = $_patient;

    /** @var CSejour $_sejour */
    $_sejour             = $_ex_object->getReferenceObject("CSejour");
    $_objects["CSejour"] = $_sejour;

    /** @var COperation $_interv */
    $_interv                = $_ex_object->getReferenceObject("COperation");
    $_objects["COperation"] = $_interv;

    $_row = [];

    foreach ($fields as $_field) {
        [$_class, $_fieldname] = $_field;

        if (isset($_objects[$_class])) {
            $_row[] = $_objects[$_class]->getFormattedValue($_fieldname);
        } else {
            $_row[] = null;
        }
    }

    // Meta fields
    $_row[] = $_ex_object->getFormattedValue("datetime_create");
    $_row[] = $_ex_object->getFormattedValue("datetime_edit");
    $_row[] = $_ex_object->getFormattedValue("owner_id");

    foreach ($ex_class->loadRefsGroups() as $_group) {
        foreach ($_group->_ref_fields as $_field) {
            if ($_field->disabled) {
                continue;
            }

            $_row[] = $_ex_object->getFormattedValue($_field->name);
        }
    }

    $csv->writeLine($_row);
}

$csv->stream($ex_class->name, true);
