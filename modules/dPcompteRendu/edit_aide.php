<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CNumcharSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\FieldSpecs\CStrSpec;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Création / Modification d'une aide à la saisie
 */
CCanDo::checkRead();

$choicepratcab = CAppUI::pref("choicepratcab");
$aide_id       = CValue::get("aide_id", CValue::post("aide_id", ''));
$class         = CValue::get("class");
$field         = CValue::get("field");
$text          = CValue::get("text", CValue::post("text", ""));
$depend_value_1 = CValue::get("depend_value_1");
$depend_value_2 = CValue::get("depend_value_2");
$class_depend_value_1 = CValue::get("class_depend_value_1");
$class_depend_value_2 = CValue::get("class_depend_value_2");

$depend_value_1 = stripslashes($depend_value_1);
$depend_value_2 = stripslashes($depend_value_2);

// Liste des users accessibles
$listPrat = new CMediusers();
$listFct = $listPrat->loadFonctions(PERM_EDIT);
$listPrat = $listPrat->loadUsers(PERM_EDIT);

$listFunc = new CFunctions();
$listFunc = $listFunc->loadSpecialites(PERM_EDIT);

$listEtab = CGroups::loadGroups(PERM_EDIT);

// Objet ciblé
$object = new $class;
$dependValues = array();

// To set the depend values always as an array (empty or not)
$helped = array();

if (isset($object->_specs[$field]) && $object->_specs[$field]->helped && !is_bool($object->_specs[$field]->helped)) {
  if (!is_array($object->_specs[$field]->helped)) {
    $helped = array($object->_specs[$field]->helped);
  }
  else {
    $helped = $object->_specs[$field]->helped;
  }
}

foreach ($helped as $i => $depend_field) {
  $key = "depend_value_" . ($i+1);
  $spec = $object->_specs[$depend_field];

  switch (get_class($spec)) {
    case CEnumSpec::class:
      $dependValues[$key] = $spec->_locales;
      break;
    case CStrSpec::class:
    case CNumcharSpec::class:
      if (${$key}) {
        $dependValues[$key][${$key}] = ${$key};
      }
      break;
    case CRefSpec::class:
      $dependValues[$key] = array("CRefSpec" => ${'class_'.$key} ? ${'class_'.$key} : $spec->class );
      if (!${'class_'.$key}) {
        ${'class_'.$key} = $spec->class;
      }
  }
}

// Liste des aides
$user_id = CValue::get("user_id", CAppUI::$user->_id);
if (!$user_id) {
  $user_id = CAppUI::$user->_id;
}

$user = new CMediusers();
$user->load($user_id);
$user->loadRefFunction();

$group = $user->_ref_function->loadRefGroup();

// Accès aux aides à la saisie de la fonction et de l'établissement
$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CAideSaisie access_function");
$access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CAideSaisie access_group");

$aidebis = new CAideSaisie();
$whereClause = "`class` = '".$class."' AND
`field` = '".$field."' AND (
  user_id     = " . $user_id;

$whereClause .= " OR function_id = " . $user->function_id;
$whereClause .= " OR group_id    = " . $group->_id;

$whereClause .= " OR (user_id IS NULL AND function_id IS NULL AND group_id IS NULL)";
$whereClause .= ")";

$where[] = $whereClause;

$orderby = "name";
$aides = $aidebis->loadList($where, $orderby);

$aide = new CAideSaisie();
$aide->load($aide_id);

if ($aide->_id) {
  if ($aide->function_id && !$access_function) {
    CAppUI::accessDenied();
  }
  if ($aide->group_id && !$access_group) {
    CAppUI::accessDenied();
  }
  $aide->loadRefUser();
  $aide->loadRefFunction();
  $aide->loadRefGroup();
}
else {
  // Nouvelle Aide à la saisie
  $aide->class        = $class;
  $aide->field        = $field;
  $text               = stripslashes($text);
  $name               = implode(" ", array_slice(explode(" ", $text), 0, 3));
  $aide->name         = $name;
  $aide->text         = $text;
  $aide->depend_value_1 = $depend_value_1;
  $aide->depend_value_2 = $depend_value_2;
  //switch(CAppUI::pref("choicepratcab")) {
    /*case "prat":*/  $aide->user_id = $user_id; //break;
    /*case "cab":*/   $aide->function_id = CAppUI::$user->function_id; //break;
    /*case "group":*/ $aide->group_id = $group->_id;
  //}

  /** @var CMbObject $_obj */

  if ($class_depend_value_1) {
    $_obj = new $class_depend_value_1;
    $_obj->load($depend_value_1);
    $aide->_vw_depend_field_1 = $_obj->_view;
  }

  if ($class_depend_value_2) {
    $_obj = new $class_depend_value_2;
    $_obj->load($depend_value_2);
    $aide->_vw_depend_field_2 = $_obj->_view;
  }
}

$fields = array(
  "user_id"     => $user_id,
  "function_id" => $user->function_id,
  "group_id"    => $group->_id
);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("aide"                , $aide);
$smarty->assign("aide_id"             , $aide_id);
$smarty->assign("dependValues"        , $dependValues);
$smarty->assign("listFunc"            , $listFunc);
$smarty->assign("listPrat"            , $listPrat);
$smarty->assign("listEtab"            , $listEtab);
$smarty->assign("aides"               , $aides);
$smarty->assign("user"                , $user);
$smarty->assign("group"               , $group);
$smarty->assign("access_function"     , $access_function);
$smarty->assign("access_group"        , $access_group);
$smarty->assign("choicepratcab"       , $choicepratcab);
$smarty->assign("fields"              , $fields);
$smarty->assign("depend_value_1"      , $depend_value_1);
$smarty->assign("depend_value_2"      , $depend_value_2);
$smarty->assign("class_depend_value_1", $class_depend_value_1);
$smarty->assign("class_depend_value_2", $class_depend_value_2);

$smarty->display("vw_edit_aides");
