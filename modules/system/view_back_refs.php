<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;

CCanDo::check();
$object_class = CValue::get("object_class");
$object_ids = CValue::get("object_ids");
CView::enforceSlave();

$objects = array();

// Load compared Object
$max = 50;
$counts = array();
foreach ($object_ids as $object_id) {
  /** @var CMbObject $object */
  $object = new $object_class;
  $object->load($object_id);
  $object->loadAllBackRefs($max);
  $objects[$object_id] = $object;
  foreach ($object->_back as $backName => $backRefs) {
    $counts[$backName] = @$counts[$backName] + $object->_count[$backName];
  }
}

// Empty object
$object = reset($objects);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("objects", $objects);
$smarty->assign("counts", $counts);
$smarty->assign("object", $object);

$smarty->display("view_back_refs.tpl");
