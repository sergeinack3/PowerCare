<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mediusers\CSecondaryFunction;

/**
 * Edit mediuser's functions
 */

CCanDo::checkRead();

$user_id    = CView::get("user_id", "ref class|CMediusers");
CView::checkin();

// Fonction principale
$user         = new CMediusers();
$user->load($user_id);
$function     = $user->loadRefFunction();
$temporary_sf = new CSecondaryFunction();
$group        = $function->loadRefGroup();
$temporary_sf->function_id   = $function->_id;
$temporary_sf->_ref_function = $function;

$perm  = new CPermObject();
$where = array(
  "user_id"      => " = '$user_id'",
  "object_class" => " IN ('CFunctions', 'CGroups')",
);

// Permissions hors-fonctions/établissements
$others_perms = $perm->loadList($where);
$perms        = array("CFunctions" => array(), "CGroups" => array());
foreach ($others_perms as $_perm) {
  $perms[$_perm->object_class][$_perm->object_id] = $_perm;
}

/*
 * Variable contenant l'ensemble des Groups, des Fonctions et des PermObject à afficher
 * Se présente sous cette forme :
 *  root => [{
 *    "object"        => Objet CGroups,
 *    "perm_object"   => Objet CPermObject lié au CGroup,
 *    "functions"     => [{
 *      "type"        => Type de la fonction : primary|secondary|permission,
 *      "object"      => Objet CFunctions,
 *      "perm_object" => Object CPermObject lié au CFunctions
 *    }]
 *  }]
 */
$groups = array(
  $function->group_id => array(
    "object"      => $group,
    "perm_object" => (isset($perms["CGroups"][$function->group_id]) ? $perms["CGroups"][$function->group_id] : null),
    "functions"   => array(
      array(
        "type"        => "primary",
        "object"      => $temporary_sf,
        "perm_object" => (isset($perms["CFunctions"][$function->_id]) ? $perms["CFunctions"][$function->_id] : null)
      )
    )
  )
);

// Retrait de la fonction des permissions hors-fonctions/établissements
foreach ($others_perms as $_key => $_perm) {
  if ($_perm->object_class === 'CFunctions' && $_perm->object_id === $function->_id) {
    unset($others_perms[$_key]);
  }
}

// Fonctions secondaires
$user->loadRefsSecondaryFunctions();
$secondary_functions = $user->_ref_secondary_functions;
// Tri de la collection
usort(
  $secondary_functions,
  function (CSecondaryFunction $a,CSecondaryFunction $b) {
    return strcmp($a->_ref_function->text, $b->_ref_function->text);
  }
);

// Flag : La fonction principale n'est pas présente en fonction secondaire
$auto_down_pf = true;
foreach ($secondary_functions as $_sec_func) {
  if (!isset($groups[$_sec_func->_ref_function->group_id])) {
    $groups[$_sec_func->_ref_function->group_id] = array(
      "object"      => $_sec_func->_ref_function->loadRefGroup(),
      "perm_object" => (isset($perms["CGroups"][$_sec_func->_ref_function->group_id]) ?
        $perms["CGroups"][$_sec_func->_ref_function->group_id] :
        null),
      "functions"   => array()
    );
  }
  $groups[$_sec_func->_ref_function->group_id]["functions"][] = array(
      "type"        =>"secondary",
      "object"      =>$_sec_func,
      "perm_object" => (isset($perms["CFunctions"][$_sec_func->function_id]) ? $perms["CFunctions"][$_sec_func->function_id] : null)
    );
  if ($user->function_id === $_sec_func->function_id) {
    $auto_down_pf = false;
  }

  foreach ($others_perms as $_key => $_perm) {
    if ($_perm->object_class === 'CFunctions' && $_perm->object_id === $_sec_func->function_id) {
      unset($others_perms[$_key]);
    }
  }
}

// Préparation des permissions hors-fonctions, hors-etablissement
$others_perms_by_type = array("CFunctions" => array(), "CGroups" => array());
foreach ($others_perms as $_perm) {
  $others_perms_by_type[$_perm->object_class][] = $_perm->object_id;
}
$where = array(
  "function_id" => CSQLDataSource::prepareIn($others_perms_by_type["CFunctions"])
);
$temporary_function = new CFunctions();
$others_functions   = $temporary_function->loadList($where);

// Permissions hors-fonctions
foreach ($others_functions as $_function) {
  if (!isset($groups[$_function->group_id])) {
    $groups[$_function->group_id] = array(
      "object"      => $_function->loadRefGroup(),
      "perm_object" => (isset($perms["CGroups"][$_function->group_id]) ? $perms["CGroups"][$_function->group_id] : null),
      "functions"   => array()
    );
  }
  $secondary_function                = new CSecondaryFunction();
  $secondary_function->function_id   = $_function->_id;
  $secondary_function->_ref_function = $_function;
  $secondary_function->user_id       = $user_id;
  $groups[$_function->group_id]["functions"][] = array(
    "type"        => "permission",
    "object"      => $secondary_function,
    "perm_object" => $perms["CFunctions"][$_function->_id]
  );
}

// Permissions hors-etablissement
$where = array(
  "group_id" => CSQLDataSource::prepareIn($others_perms_by_type["CGroups"]),
);
$temporary_group = new CGroups();
$others_groups   = $temporary_group->loadList($where);

foreach ($others_groups as $_group) {
  if (!isset($groups[$_group->_id])) {
    $groups[$_group->_id] = array(
      "object"      => $_group,
      "perm_object" => null,
      "functions"   => array()
    );
  }

  $groups[$_group->_id]["perm_object"] = $perms["CGroups"][$_group->_id];
}

$empty_perm = new CPermObject();

$smarty = new CSmartyDP();

$smarty->assign("user",         $user);
$smarty->assign("groups",       $groups);
$smarty->assign("auto_down_pf", $auto_down_pf);
$smarty->assign("empty_perm",   $empty_perm);

$smarty->display("inc_functions");