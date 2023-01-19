<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassEvent;

$patient_id     = CView::get("patient_id", "num pos");
$ext_cabinet_id = CView::get("cabinet_id", "num");
$context_guid   = CView::get("context_guid", "str");

CView::checkin();

$patient = new CPatient();
$patient->load($patient_id);

$curr_user = CMediusers::get();

// Le contexte par défaut est le patient
$context                = $patient;
$context->_praticien_id = $curr_user->_id;

if ($context_guid) {
    $context = CMbObject::loadFromGuid($context_guid);

    if ($context instanceof CSejour || $context instanceof COperation || $context instanceof CConsultation) {
        CAccessMedicalData::logAccess($context);
    }
}

$prat_id = $curr_user->_id;

switch ($context->_class) {
    case "CConsultation":
        $context->loadRefPlageConsult();
        $context->_ref_chir->loadRefFunction();
        $prat_id = $context->_ref_chir->_id;
        break;
    case "CConsultAnesth":
        $context->loadRefConsultation()->loadRefPlageConsult();
        $context->loadRefChir();
        $prat_id = $context->_ref_consultation->_ref_chir->_id;
        break;
    case "CSejour":
        $context->loadRefPraticien()->loadRefFunction();
        $prat_id = $context->praticien_id;
        break;
    case "COperation":
        $context->loadRefPlageOp();
        $context->loadRefChir()->loadRefFunction();
        $prat_id = $context->chir_id;
        break;
    default:
}

$prat = CMediusers::get($prat_id);

// Chargement des formulaires
$group_id              = CGroups::loadCurrent()->_id;
$where                 = [
    "group_id = '$group_id' OR group_id IS NULL",
];
$ex_class              = new CExClass();
CExClass::$_list_cache = $ex_class->loadList($where, "name");

// Loading the events
$ex_classes_creation = [];
$ex_class_events     = [];
$_ex_class_creation  = [];

foreach (CExClass::$_list_cache as $_ex_class_id => $_ex_class) {
    if (!$_ex_class->conditional) {
        $_ex_class_creation[] = $_ex_class_id;
    }
}

$where = [
    "ex_class_event.ex_class_id" => CSQLDataSource::get("std")->prepareIn($_ex_class_creation),
    "ex_class_event.disabled"    => "= '0'",
];

/** @var CExClassEvent[] $ex_class_events_by_ref */
$ex_class_event         = new CExClassEvent();
$ex_class_events_by_ref = $ex_class_event->loadList($where);
CStoredObject::massLoadBackRefs($ex_class_events_by_ref, "constraints");

foreach ($ex_class_events_by_ref as $_ex_class_event) {
    $_key = "$_ex_class_event->host_class/$_ex_class_event->ex_class_id";

    /** @var CExClassEvent[] $_ex_class_events */
    if (!array_key_exists($_key, $ex_class_events)) {
        $ex_class_events[$_key] = [];
    }

    $ex_class_events[$_key][] = $_ex_class_event;
}

foreach ($_ex_class_creation as $_ex_class_id) {
    if (!isset($ex_class_events["$context->_class/$_ex_class_id"])) {
        continue;
    }

    $_ex_class_events = $ex_class_events["$context->_class/$_ex_class_id"];

    foreach ($_ex_class_events as $_id => $_ex_class_event) {
        if (!$_ex_class_event->checkConstraints($context)) {
            unset($_ex_class_events[$_id]);
        }
    }

    if (count($_ex_class_events)) {
        $ex_classes_creation[$_ex_class_id] = $_ex_class_events;
    }
}

$smarty = new CSmartyDP();

$smarty->assign("patient", $patient);
$smarty->assign("context", $context);
$smarty->assign("curr_user", $curr_user);
$smarty->assign("prat", $prat);
if ($context->_class === "CEvenementPatient" && CModule::getActive("oxCabinet")) {
    $categorie = CAppUI::gconf("oxCabinet CEvenementPatient categorie_{$context->type}_default");
    $smarty->assign("categorie", $categorie);
}
$smarty->assign("files_categories", CFilesCategory::listCatClass($context->_class));
$smarty->assign("ex_classes", CExClass::$_list_cache);
$smarty->assign("ex_classes_creation", $ex_classes_creation);
$smarty->assign("ext_cabinet_id", $ext_cabinet_id);

$smarty->display("inc_add_doc");
