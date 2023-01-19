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
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExConcept;

CCanDo::checkAdmin();

$file      = CValue::read($_FILES, 'import');
$separator = CValue::post("separator", ',');
$enclosure = CValue::post("enclosure", '"');

$prop_map = array(
  "binaire"       => "bool",
  "binaire ssq"   => "bool",
  "binaire / ssq" => "bool",

  "liste fermée"       => "enum vertical|1 typeEnum|radio",
  "liste fermée ssq"   => "enum vertical|1 typeEnum|radio",
  "liste fermée / ssq" => "enum vertical|1 typeEnum|radio",

  "texte court" => "str",
  "texte long"  => "text",
  "date/heure"  => "dateTime",
  "timestamp"   => "dateTime",

  "numérique" => "float",
);

function reduce_whitespace($str) {
  return preg_replace("/\s+/", " ", $str);
}

if (!$file) {
  CAppUI::setMsg("Aucun fichier fourni", UI_MSG_WARNING);
}
else {
  CMbObject::$useObjectCache = false;
  $fp                        = fopen($file['tmp_name'], 'r');

  $keys = array(
    "concept_name_old", "concept_name", "concept_type",
    "list_name_old", "list_name",
    "tag_old", "tag_name_1", "tag_name_2",
    "field_name", "void",
  );

  $multiline = array();
  $line      = array_fill_keys($keys, "");

  $current_class = null;
  $current_group = null;

  $line_number = 0;
  while ($current_line = fgetcsv($fp, null, $separator, $enclosure)) {
    $line_number++;

    $current_line = array_map("trim", $current_line);
    $current_line = array_map("reduce_whitespace", $current_line);
    $current_line = array_combine($keys, $current_line);

    foreach ($current_line as $_key => $_value) {
      if (in_array($_key, $multiline) && $_value == "") {
        $current_line[$_key] = $line[$_key];
      }
    }

    $line = $current_line;

    // EX CLASS
    if (empty($line["field_name"]) && empty($line["concept_name"])) {
      $current_class = new CExClass();
      $class         = reset($line);

      $ds    = $current_class->_spec->ds;
      $where = array(
        "name" => $ds->prepare("=%", $class),
      );
      $current_class->loadObject($where);

      if (!$current_class->_id) {
        $current_class->name       = $class;
        $current_class->host_class = "CMbObject";
        $current_class->event      = "void";
        $current_class->disabled   = 1;
        //$current_class->required = 0;
        $current_class->conditional = ((stripos($class, "SSQ") !== false) ? 1 : 0);

        if ($msg = $current_class->store()) {
          CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
          continue;
        }
        else {
          CAppUI::setMsg("$current_class->_class-msg-create", UI_MSG_OK);
        }
      }

      $current_class->loadRefsGroups();
      $current_group = reset($current_class->_ref_groups);
      continue;
    }

    if (!$current_group || !$current_group->_id) {
      CAppUI::setMsg("Ligne $line_number sautée", UI_MSG_OK);
      continue;
    }

    // CONCEPT
    $concept = new CExConcept();
    $ds      = $concept->_spec->ds;
    $where   = array(
      "name" => $ds->prepare("=%", $line["concept_name"]),
    );

    $concept->loadObject($where);

    if (!$concept->_id) {
      CAppUI::setMsg("Ligne $line_number : concept non trouvé : <strong>{$line['concept_name']}</strong>", UI_MSG_WARNING);
      continue;
    }

    // FIELD
    $field                     = new CExClassField();
    CExClassField::$_load_lite = true;
    $field_name                = empty($line["field_name"]) ? $line["concept_name"] : $line["field_name"];

    $ds    = $field->_spec->ds;
    $where = array(
      "ex_class_field_translation.std" => $ds->prepare("=%", $field_name),
      "ex_class_field.ex_group_id"     => $ds->prepare("=%", $current_group->_id),
    );

    $ljoin = array(
      "ex_class_field_translation" => "ex_class_field_translation.ex_class_field_id = ex_class_field.ex_class_field_id",
    );

    $field->loadObject($where, null, null, $ljoin);

    if (!$field->_id) {
      $field->name        = uniqid("f");
      $field->_locale     = $field_name;
      $field->ex_group_id = $current_group->_id;
      $field->concept_id  = $concept->_id;

      if ($msg = $field->store()) {
        CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
        continue;
      }
      else {
        CAppUI::setMsg("$field->_class-msg-create", UI_MSG_OK);
      }
    }
  }

  fclose($fp);

  CAppUI::setMsg("Import terminé", UI_MSG_OK);
}

$smarty = new CSmartyDP();
$smarty->assign("message", CAppUI::getMsg());
$smarty->display("inc_import.tpl");
