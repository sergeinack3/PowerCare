<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$aide_id = CView::get("aide_id", "ref class|CAideSaisie", true);
$user_id = CView::get("user_id", "ref class|CMediusers", true);
CView::checkin();

// Aide sélectionnée
$aide = new CAideSaisie();
$aide->load($aide_id);

// Accès aux aides à la saisie de la fonction et de l'établissement
$module = CModule::getActive("dPcompteRendu");
$is_admin = $module && $module->canAdmin();
$access_function = $is_admin || CAppUI::gconf("dPcompteRendu CAideSaisie access_function");
$access_group    = $is_admin || CAppUI::gconf("dPcompteRendu CAideSaisie access_group");

if ($aide->_id) {
  if ($aide->function_id && !$access_function) {
    CAppUI::accessDenied();
  }
  if ($aide->group_id && !$access_group) {
    CAppUI::accessDenied();
  }
}
else {
  if (!CMediusers::get()->isAdmin()) {
    $aide->user_id = $user_id;
  }
}

$aide->loadRefUser();
$aide->loadRefFunction();
$aide->loadRefGroup();
$aide->loadRefsCodesLoinc();
$aide->loadRefsCodesSnomed();

$classes = array_flip(CApp::getInstalledClasses(null, true));

$listTraductions = array();

// Chargement des champs d'aides a la saisie
foreach ($classes as $class => &$infos) {
  $object = new $class;
  $listTraductions[$class] = CAppUI::tr($object->_class);

  $infos = array();
  foreach ($object->_specs as $field => $spec) {
    if (!isset($spec->helped)) {
      continue;
    }
    $info =& $infos[$field];
    $helped = $spec->helped;

    if (!is_array($helped)) {
      $info = null;
      continue;
    }

    foreach ($helped as $i => $depend_field) {
      $key = "depend_value_" . ($i+1);
      $info[$key] = array();
      $list = &$info[$key];
      $list = array();
      // Because some depend_fields are not enums (like object_class from CCompteRendu)
      if (!isset($object->_specs[$depend_field]->_list)) {
        continue;
      }
      foreach ($object->_specs[$depend_field]->_list as $value) {
        $locale = "$object->_class.$depend_field.$value";
        $list[$value] = $locale;
        $listTraductions[$locale] = CAppUI::tr($locale);
      }
    }
  }
}

CMbArray::removeValue(array(), $classes);

$smarty = new CSmartyDP();

$smarty->assign("aide"            , $aide);
$smarty->assign("listTraductions" , $listTraductions);
$smarty->assign("classes"         , $classes);
$smarty->assign("access_function" , $access_function);
$smarty->assign("access_group"    , $access_group);

$smarty->display("inc_edit_aide");
