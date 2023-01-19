<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExClassFieldGroup;

CCanDo::checkAdmin();

$file      = CValue::read($_FILES, 'import');
$separator = CValue::post("separator", ',');
$enclosure = CValue::post("enclosure", '"');

function reduce_whitespace($str) {
  return preg_replace("/\s+/", " ", $str);
}

if (!$file) {
  CAppUI::setMsg("Aucun fichier fourni", UI_MSG_WARNING);
}
else {
  //CMbObject::$useObjectCache = false;
  $fp = fopen($file['tmp_name'], 'r');

  $keys      = array(
    "group_1", "group_2", "field_name",
  );
  $multiline = array(/*"group_1", "group_2"*/);
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
    if (
      empty($line["group_1"]) &&
      empty($line["group_2"]) &&
      ($line["field_name"] === CMbString::upper($line["field_name"]))
    ) { // we assume exclasses are uppercase

      $current_group = null;
      $current_class = new CExClass();

      $class = $line["field_name"];

      $ds    = $current_class->_spec->ds;
      $where = array(
        "name" => $ds->prepare("=%", $class),
      );
      $current_class->loadObject($where);

      if (!$current_class->_id) {
        CAppUI::setMsg("Ligne $line_number : formulaire non trouvé ($class)", UI_MSG_WARNING);
      }

      continue;
    }

    // EX CLASS FIELD GROUP
    if (!empty($line["group_1"]) && !empty($current_class->_id)) {
      $current_group = new CExClassFieldGroup();

      $group_name = $line["group_1"];

      $ds    = $current_group->_spec->ds;
      $where = array(
        "ex_class_id" => $ds->prepare("=%", $current_class->_id),
        "name"        => $ds->prepare("=%", $group_name),
      );
      $current_group->loadObject($where);

      if (!$current_group->_id) {
        $current_group->name        = $group_name;
        $current_group->ex_class_id = $current_class->_id;

        if ($msg = $current_group->store()) {
          CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
          continue;
        }
        else {
          CAppUI::setMsg("$current_group->_class-msg-create", UI_MSG_OK);
        }
      }
    }

    if (empty($current_class->_id) || empty($current_group->_id)) {
      continue;
    }

    $field      = new CExClassField();
    $field_name = $line["field_name"];

    $ds    = $field->_spec->ds;
    $where = array(
      "ex_class_field_translation.std" => $ds->prepare("=%", $field_name),
      "ex_class.ex_class_id"           => $ds->prepare("=%", $current_class->_id),
    );

    $ljoin = array(
      "ex_class_field_translation" => "ex_class_field_translation.ex_class_field_id = ex_class_field.ex_class_field_id",
      "ex_class_field_group"       => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
      "ex_class"                   => "ex_class.ex_class_id = ex_class_field_group.ex_class_id",
    );

    $field->loadObject($where, null, null, $ljoin);

    if (!$field->_id) {
      CAppUI::setMsg("Ligne $line_number : champ non trouvé ($field_name)", UI_MSG_WARNING);
      continue;
    }

    // mise a jour du groupe du champ
    if ($field->ex_group_id != $current_group->_id) {
      $field->ex_group_id = $current_group->_id;

      if ($msg = $field->store()) {
        CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
        continue;
      }
      else {
        CAppUI::setMsg("$field->_class-msg-modify", UI_MSG_OK);
      }
    }
  }

  fclose($fp);

  CAppUI::setMsg("Import terminé", UI_MSG_OK);
}

$smarty = new CSmartyDP();
$smarty->assign("message", CAppUI::getMsg());
$smarty->display("inc_import.tpl");
