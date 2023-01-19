<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpecFact;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CBoolSpec;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExConcept;
use Ox\Mediboard\System\Forms\CExList;
use Ox\Mediboard\System\Forms\CExListItemsOwner;

CCanDo::checkEdit();

$prop      = CValue::get("prop");
$spec_type = CValue::get("_spec_type");

$form_name     = CValue::get("form_name");
$context_guid  = CValue::get("context_guid");
$ex_concept_id = CValue::get("ex_concept_id");

// Cas du choix d'une liste
$concept_type = CValue::get("_concept_type");
$ex_list_id   = CValue::get("ex_list_id");
$multiple     = CValue::get("_multiple");

/** @var CExListItemsOwner $context */
$context = CMbObject::loadFromGuid($context_guid);
$context->loadView();

$list_owner = $context->getRealListOwner();
$list_owner->loadView();

if ($concept_type == "list" && !$ex_concept_id && $context instanceof CExConcept) {
    $ex_list = new CExList();
    $ex_list->load($ex_list_id);
    $ex_list_items = $ex_list->loadRefItems();
    $prop          = ($multiple ? "set" : "enum") . " list|" . implode("|", CMbArray::pluck($ex_list_items, "_id"));
}

$prop = stripslashes($prop);

$prop_type = explode(" ", $prop);
$prop_type = reset($prop_type);

if ($spec_type) {
    if (!array_key_exists($prop_type, CMbFieldSpecFact::$classes)) {
        $prop = "$spec_type $prop";
    } else {
        if (strpos($prop, " ") !== false) {
            $prop = $spec_type . " " . substr($prop, strpos($prop, " ") + 1);
        } else {
            $prop = $spec_type;
        }
    }
}

$spec = CExConcept::getConceptSpec($prop);

if (!$spec->prop) {
    CAppUI::stepMessage(UI_MSG_ALERT, "Enregistrez le champ avant de pouvoir changer les propriétés");

    return;
}

// UGLY hack because of the default value of the Boolspec
if ($spec instanceof CBoolSpec && strpos($prop, "default|") === false) {
    $spec->default = null;
}

if ($spec instanceof CEnumSpec || $spec instanceof CBoolSpec) {
    if ($spec->typeEnum === null || !in_array($spec->typeEnum, $spec->_options["typeEnum"])) {
        $spec->typeEnum = reset($spec->_options["typeEnum"]);
    }
}

$boolean = [
    "notNull",
    "vertical",
    "progressive",
    "cascade",
];

$options = $spec->_options;

foreach (CExClassField::$_props_exclude as $_exclude) {
    unset($options[$_exclude]);
}

function order_items($a, $b)
{
    $order = $GLOBALS["items"];

    $key_a = array_search($a, $order);
    $key_b = array_search($b, $order);

    return ($key_a === false ? 1000 : $key_a) - ($key_b === false ? 1000 : $key_b);
}

CExConcept::orderSpecs($options);

$items_sub = [];
$items_all = [];

if ($spec instanceof CEnumSpec) {
    if ($list_owner->_id) {
        $list_owner->updateEnumSpec($spec);
        $prop .= " " . implode("|", $spec->_list);
    }

    $items_sub = $spec->_list;
    $items_all = $spec->_list;
}

// to get the right locales
/*
if ($spec instanceof CEnumSpec) {
  $ex_field = new CExClassField;
  $ex_field->load($ex_field_id);

  $enum_trans = $ex_field->loadRefEnumTranslations();

  foreach($enum_trans as $_enum_trans) {
    $_enum_trans->updateLocales();
  }

  if ($ex_field->ex_class_id) {
    $ex_object = new CExObject($ex_field->ex_class_id);

    if ($ex_object->_specs[$field] instanceof CEnumSpec) {
      $spec = $ex_object->_specs[$field];
    }
  }

  else {
    // A second timpe to get the enum locales
    $spec = @CMbFieldSpecFact::getSpecWithClassName($class, $field, $prop);
  }
}*/

$triggerables_cond   = [];
$triggerables_others = [];

if ($context instanceof CExClassField) {
    $context->loadTriggeredData();
    $ex_class = $context->loadRefExClass();
    if (!$ex_class->conditional) {
        $triggerable = new CExClass();

        $group_id = CGroups::loadCurrent()->_id;
        $where    = [
            "group_id = '$group_id' OR group_id IS NULL",
            "conditional"            => "= '1'",
            $triggerable->_spec->key => "!= '$ex_class->_id'",
        ];

        // TODO charger les ex_class qui ont un event avec la meme classe que ... quel evenement ???

        $triggerables_cond = $triggerable->loadList($where, "conditional DESC, name");

        $where["conditional"] = "= '0'";
        $where["group_id"]    = "= '$group_id' OR group_id IS NULL";
        $triggerables_others  = $triggerable->loadList($where, "conditional DESC, name");
    }

    if (!empty($context->concept_id)) {
        if (!empty($context->_ref_concept->_ref_ex_list)) {
            $items_all = array_keys($context->_ref_concept->_ref_ex_list->_ref_items);
        } else {
            $items_all = array_keys($context->_ref_concept->_ref_items);
        }
    }
}

$GLOBALS["items"] = $items_sub;
usort($items_all, "order_items");

$instances = null;
$classes   = $spec instanceof CRefSpec ? CApp::getMbClasses($instances, true) : [];

$smarty = new CSmartyDP();
$smarty->assign("prop", $prop);
$smarty->assign("spec", $spec);
$smarty->assign("options", $options);
$smarty->assign("boolean", CExClassField::$_props_boolean);
$smarty->assign("items_all", $items_all);
$smarty->assign("items_sub", $items_sub);
$smarty->assign("form_name", $form_name);
$smarty->assign("classes", $classes);
$smarty->assign("list_owner", $list_owner);
$smarty->assign("context", $context);
$smarty->assign("triggerables_cond", $triggerables_cond);
$smarty->assign("triggerables_others", $triggerables_others);
$smarty->display("inc_edit_ex_field_spec2.tpl");
