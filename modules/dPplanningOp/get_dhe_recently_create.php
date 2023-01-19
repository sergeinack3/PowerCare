<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CObjectClass;

CCanDo::checkRead();

global $g;
$rpps            = CView::get("rpps", "str");
$cabinet_id      = CView::get("cabinet_id", "num");
$context_guid    = CView::get("context_guid", "str");
$ext_patient_id  = CView::get("ext_patient_id", "str");

CView::checkin();

//Recherche du praticien
$mediuser = new CMediusers();
$mediuser->actif = "1";
$mediuser->rpps = $rpps;
$mediuser->loadMatchingObjectEsc();

//Recherche du patient
$idexs = CIdSante400::getMatches("CPatient", "ext_patient_id-$cabinet_id", null);

if (!$mediuser->_id || !count($idexs)) {
  $json = ["object_guid"  => null, "cabinet_id"  => $cabinet_id];
  CApp::json($json);
}

$ljoin = array();
$where = array();
$where["sejour.group_id"]   = " = '$g'";
$where["sejour.patient_id"]   = CSQLDataSource::prepareIn(CMbArray::pluck($idexs, "object_id"));
$where["sejour.praticien_id"] = " = '$mediuser->_id'";

list($context_class, $context_id) = explode("-", $context_guid);
// Renvoie des informations des éléments mis à jour
if ($context_id != "0") {
  $object = CStoredObject::loadFromGuid($context_guid);
  $parent = $object instanceof COperation ? $object->loadRefSejour() : null;
  list($libelle, $libelle_parent) = CSejour::getLibelles($object, $parent);
  $json = [
    "object_guid" => $object->_guid,
    "libelle"     => $libelle,
    "date"        => $context_class == "CSejour" ? CMbDT::date($object->entree) : $object->date,
    "cabinet_id"  => $cabinet_id,
    "parent_guid" => $parent ? $parent->_guid : null,
    "parent_libelle" => $parent ? $libelle_parent : null,
    "parent_date" => $parent ? CMbDT::date($parent->entree) : null,
  ];
  CApp::json($json);
}

$table_action = "user_log";
$lien_action_object = "$table_action.object_class = '$context_class'";
$activer_user_action = CAppUI::conf("activer_user_action");
if ($activer_user_action) {
  $table_action = "user_action";
  $object_class = new CObjectClass();
  $object_class->object_class = $context_class;
  $object_class->loadMatchingObject();
  $lien_action_object = "$table_action.object_class_id = '$object_class->_id'";
}

$where["$table_action.type"]       = " = 'create'";
$where["$table_action.date"]       = " >= '".CMbDT::dateTime("-5 minutes")."'";
$where["id_sante400.tag"]     = " = 'cabinet_id'";
$where["id_sante400.id400"]   = " = '$cabinet_id'";
$where["id_sante400.id_sante400_id"] = " IS NOT NULL";

$parent = null;
if ($context_class == "CSejour") {
  $ljoin["id_sante400"] = "id_sante400.object_id = sejour.sejour_id AND id_sante400.object_class = 'CSejour'";
  $ljoin["$table_action"] = "$table_action.object_id = sejour.sejour_id AND $lien_action_object";
  $object = new CSejour();
  $object->loadObject($where, "$table_action.date DESC", "sejour.sejour_id", $ljoin);
}
else {
  $ljoin["sejour"] = "sejour.sejour_id = operations.sejour_id";
  $ljoin["$table_action"] = "$table_action.object_id = operations.operation_id AND $lien_action_object";
  $ljoin["id_sante400"] = "id_sante400.object_id = operations.operation_id AND id_sante400.object_class = 'COperation'";
  $object = new COperation();
  $object->loadObject($where, "$table_action.date DESC", "operations.operation_id", $ljoin);
  $parent = $object->loadRefSejour();
}

list($libelle, $libelle_parent) = CSejour::getLibelles($object, $parent);
$json = [
  "object_guid" => $object->_guid,
  "libelle"     => $libelle,
  "date"        => $context_class == "CSejour" ? CMbDT::date($object->entree) : $object->date,
  "cabinet_id"  => $cabinet_id,
  "parent_guid" => $parent ? $parent->_guid : null,
  "parent_libelle" => $parent ? $libelle_parent : null,
  "parent_date" => $parent ? CMbDT::date($parent->entree) : null,
];
CApp::json($json);
