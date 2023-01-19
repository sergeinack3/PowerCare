<?php
/**
 * @package Mediboard\Developpement\ClassMap
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();
$class_name = CView::get('class', 'str');
$class_name = str_replace("\\\\", "\\", $class_name);
CView::checkin();

$class_map = CClassMap::getInstance();
$map       = $class_map->getClassMap($class_name);

$refs = $class_map->getClassRef();
$ref  = $refs[$class_name] ?? null;

$msg ="Class does not exist.";
$class_exists = "error";
if (class_exists($class_name)) {
  $msg = "Class exists.";
  $class_exists = "success";
}
elseif (interface_exists($class_name)) {
  $msg = "Interface exists.";
  $class_exists = "success";
}
elseif (trait_exists($class_name)) {
  $msg = "Trait exists.";
  $class_exists = "success";
}


$smarty = new CSmartyDP();
$smarty->assign("msg", $msg);
$smarty->assign("class_exists", $class_exists);
$smarty->assign("map", json_encode($map, JSON_PRETTY_PRINT));
$smarty->assign("ref", $ref !== null ? json_encode($ref, JSON_PRETTY_PRINT) : 'Class isn\'t a CModelObject child');
$smarty->display('vw_class_map_detail');