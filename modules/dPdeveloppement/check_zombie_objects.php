<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;

CCanDo::checkRead();

$class = CValue::getOrSession('class', 'CPatient');
$objects_count = CValue::getOrSession('objects_count', 20);

$count = 0;
$zombies = array();

/** @var CStoredObject $object */
$object = new $class;
$installed_classes = CApp::getInstalledClasses(array(), true);
$ds = $object->_spec->ds;

if ($object->_spec->table) {
  $object->makeAllBackSpecs();
  foreach ($object->_backSpecs as $name => $back_spec) {
    /** @var CStoredObject $back_object */
    $back_object = new $back_spec->class;
    $fwd_spec = $back_object->_specs[$back_spec->field];

    // Check the back ref only if the class's module is installed
    if (!in_array($back_spec->class, $installed_classes)) {
      continue;
    }
    
    $queryCount = "SELECT COUNT(*) AS total ";
    $queryLoad  = "SELECT `back_obj`.* ";
    
    $query = "FROM `{$back_object->_spec->table}` AS `back_obj`
      LEFT JOIN `{$object->_spec->table}` AS `obj` ON `obj`.`{$object->_spec->key}` = `back_obj`.`{$back_spec->field}`
      WHERE `obj`.`{$object->_spec->key}` IS NULL 
      AND `back_obj`.`{$back_spec->field}` IS NOT NULL";
    
    if ($field_meta = $fwd_spec->meta) {
      $query .= "\n AND `back_obj`.`$field_meta` = '$class'";
    }
    
    $queryCount .= $query;
    $queryLoad  .= $query;
    
    $row = $ds->fetchArray($ds->exec($queryCount));
    $zombies[$name] = array(
      'count' => $row['total'],
      'objects' => $back_object->loadQueryList($queryLoad, " LIMIT $objects_count"),
    );
  }
}

$smarty = new CSmartyDP();

$smarty->assign("object", $object);
$smarty->assign("zombies", $zombies);
$smarty->assign("classes", $installed_classes);
$smarty->assign("class", $class);
$smarty->assign("objects_count", $objects_count);

$smarty->display("check_zombie_objects.tpl");
