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
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\CTag;
use Ox\Mediboard\System\CTagItem;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExList;
use Ox\Mediboard\System\Forms\CExListItem;

CCanDo::checkAdmin();

$object_class = CValue::post("object_class");
$file         = CValue::read($_FILES, 'import');
$separator    = CValue::post("separator", ',');
$enclosure    = CValue::post("enclosure", '"');

/*
TRUNCATE `ex_concept`;
TRUNCATE `ex_list`;
TRUNCATE `ex_list_item`;
TRUNCATE `tag`;
TRUNCATE `tag_item`;
 */

$prop_map = array(
  "binaire"       => "bool",
  "binaire ssq"   => "bool",
  "binaire / ssq" => "bool",

  "liste fermée"       => "enum vertical|1 typeEnum|radio",
  "liste fermée ssq"   => "enum vertical|1 typeEnum|radio",
  "liste fermée / ssq" => "enum vertical|1 typeEnum|radio",

  "texte court" => "text",
  "texte long"  => "text",
  "date/heure"  => "dateTime",
  "timestamp"   => "dateTime",

  "numérique" => "float",
);

function reduce_whitespace($str) {
  return preg_replace("/\s+/", " ", $str);
}

$line_number = 0;

if (!$object_class) {
  CAppUI::setMsg("Veuillez choisir un type d'objet", UI_MSG_WARNING);
}
else {

  if (!$file) {
    CAppUI::setMsg("Aucun fichier fourni", UI_MSG_WARNING);
  }
  else {
    CMbObject::$useObjectCache = false;
    $fp                        = fopen($file['tmp_name'], 'r');

    switch ($object_class) {

      ////////////////// LIST ///////////////////////
      case "CExList":
        $keys      = array("list_name", "list_coded", "item_code", "item_name", "list_multiple");
        $multiline = array("list_name", "list_coded");
        $line      = array_fill_keys($keys, "");

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

          // LIST
          $list  = new CExList();
          $ds    = $list->_spec->ds;
          $where = array(
            "name" => $ds->prepare("=%", $line["list_name"]),
          );

          $list->loadObject($where);

          $list->coded    = (($line["item_code"] != "") ? 1 : 0);
          $list->multiple = ((!$list->coded || ($line["list_multiple"] != "")) ? 1 : 0);

          if (!$list->_id) {
            $list->name = $line["list_name"];

            if ($msg = $list->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
              continue;
            }
            else {
              CAppUI::setMsg("$list->_class-msg-create", UI_MSG_OK);
            }
          }
          else {
            if ($msg = $list->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
              continue;
            }
            else {
              CAppUI::setMsg("$list->_class-msg-modify", UI_MSG_OK);
            }
          }

          // LIST ITEM
          $list_item = new CExListItem();
          $ds        = $list_item->_spec->ds;
          $where     = array(
            "list_id" => $ds->prepare("=%", $list->_id),
            "name"    => $ds->prepare("=%", $line["item_name"]),
          );

          $list_item->loadObject($where);

          if (!$list_item->_id) {
            $list_item->list_id = $list->_id;
            $list_item->name    = $line["item_name"];
            $list_item->code    = $line["item_code"];

            if ($msg = $list_item->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
              continue;
            }
            else {
              CAppUI::setMsg("$list_item->_class-msg-create", UI_MSG_OK);
            }
          }
        }

        break;

      ////////////////// CONCEPT ///////////////////////
      case "CExConcept":
        $keys      = array("tag_name_1", "tag_name_2", "concept_name", "concept_type", "list_name", "form");
        $multiline = array("tag_name_1", "tag_name_2");
        $line      = array_fill_keys($keys, "");

        while ($current_line = fgetcsv($fp, null, $separator, $enclosure)) {
          $line_number++;

          $current_line = array_slice($current_line, 0, count($keys));
          $current_line = array_map("trim", $current_line);
          $current_line = array_map("reduce_whitespace", $current_line);
          $current_line = array_combine($keys, $current_line);

          foreach ($current_line as $_key => $_value) {
            if (in_array($_key, $multiline) && $_value == "") {
              $current_line[$_key] = $line[$_key];
            }
          }

          $line = $current_line;

          // TAG LEVEL 1
          $tag1  = new CTag();
          $ds    = $tag1->_spec->ds;
          $where = array(
            "name"         => $ds->prepare("=%", $line["tag_name_1"]),
            "object_class" => $ds->prepare("=%", $object_class),
          );

          $tag1->loadObject($where);

          if (!$tag1->_id) {
            $tag1->name         = $line["tag_name_1"];
            $tag1->object_class = $object_class;

            if ($msg = $tag1->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
              continue;
            }
            else {
              CAppUI::setMsg("$tag1->_class-msg-create", UI_MSG_OK);
            }
          }

          // TAG LEVEL 2
          $tag2  = new CTag;
          $ds    = $tag2->_spec->ds;
          $where = array(
            "name"         => $ds->prepare("=%", $line["tag_name_2"]),
            "object_class" => $ds->prepare("=%", $object_class),
          );

          $tag2->loadObject($where);

          if (!$tag2->_id) {
            $tag2->name         = $line["tag_name_2"];
            $tag2->object_class = $object_class;
            $tag2->parent_id    = $tag1->_id;

            if ($msg = $tag2->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
              continue;
            }
            else {
              CAppUI::setMsg("$tag2->_class-msg-create", UI_MSG_OK);
            }
          }

          // CONCEPT
          $concept_prop = CValue::read($prop_map, CMbString::lower($line["concept_type"]));
          if (!$concept_prop) {
            CAppUI::setMsg("Ligne $line_number : type de concept invalide : <strong>{$line['concept_type']}</strong>", UI_MSG_WARNING);
            continue;
          }

          // If list name provided, it needs to exist
          if ($line["list_name"]) {
            $_list       = new CExList;
            $_list->name = $line["list_name"];
            if (!$_list->loadMatchingObject()) {
              CAppUI::setMsg("Ligne $line_number : nom de liste introuvable : <strong>{$line['list_name']}</strong>", UI_MSG_WARNING);
              continue;
            }
          }

          $concept = new CExConcept();
          $ds      = $concept->_spec->ds;
          $where   = array(
            "name" => $ds->prepare("=%", $line["concept_name"]),
          );

          $concept->loadObject($where);

          if (!$concept->_id) {
            if ($_list && $_list->_id && $line["list_name"]) {
              $concept->ex_list_id = $_list->_id;
              if ($_list->multiple) {
                $concept_prop = "set vertical|1 typeEnum|checkbox";
              }
            }

            $concept->name = $line["concept_name"];
            $concept->prop = $concept_prop;

            if ($msg = $concept->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
              continue;
            }
            else {
              CAppUI::setMsg("$concept->_class-msg-create", UI_MSG_OK);
            }
          }
          else {
            if ($_list && $_list->_id && $line["list_name"]) {
              $concept->ex_list_id = $_list->_id;

              if ($_list->multiple) {
                $concept->prop = "set vertical|1 typeEnum|checkbox";

                if ($msg = $concept->store()) {
                  CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
                  continue;
                }
                else {
                  CAppUI::setMsg("$concept->_class-msg-modify", UI_MSG_OK);
                }
              }
            }
          }

          // TAG BINDING
          $tag_item = new CTagItem();
          $tag_item->setObject($concept);
          $tag_item->tag_id = $tag2->_id;

          $tag_item->loadMatchingObject();

          if (!$tag_item->_id) {
            if ($msg = $tag_item->store()) {
              CAppUI::setMsg("Ligne $line_number : $msg", UI_MSG_WARNING);
            }
            else {
              CAppUI::setMsg("$tag_item->_class-msg-create", UI_MSG_OK);
            }
          }
        }

        break;
    }

    fclose($fp);

    CAppUI::setMsg("Import terminé", UI_MSG_OK);
  }
}

$smarty = new CSmartyDP();
$smarty->assign("message", CAppUI::getMsg());
$smarty->display("inc_import.tpl");
