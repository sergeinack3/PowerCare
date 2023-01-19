<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;

CCanDo::checkAdmin();

$object_class = CView::post("object_class", "str");
$assure       = CView::post("assure", "bool");
$limit        = CView::post("limit", "num");

CView::checkin();

if (!$object_class) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Veuillez choisir une classe !");
  CApp::rip();
}

$object = new $object_class();

$field_prenom = $object->getPrenomFieldName();
$field_sexe   = $object->getSexFieldName();

// Cas particulier de l'assuré qui est stocké dans la même table que le patient...
if ($assure) {
  $field_prenom = "assure_prenom";
  $field_sexe   = "assure_sexe";
}

$where = array(
  $field_prenom => "IS NOT NULL",
  $field_sexe   => "IS NULL OR $field_sexe = 'u'"
);

$objects = $object->loadList($where, $field_prenom, $limit);

$count = 0;

$errors = array();

foreach ($objects as $_object) {
  $msg = $_object->store();

  if (!$msg && $_object->$field_sexe && $_object->$field_sexe != "u") {
    $count++;
    continue;
  }

  $errors[] = array(
    "object" => $_object,
    "msg"    => $msg ? $msg : "Sexe indéterminé !"
  );
}

CAppUI::stepAjax("$count / " . count($objects) . " paires traitées");

foreach ($errors as $_error) {
  $object = $_error["object"];
  $msg    = $_error["msg"];
  CAppUI::stepAjax("Erreur pour l'objet $object->_id : $msg", UI_MSG_WARNING);
}