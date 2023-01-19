<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$class = CValue::get('class');
$id    = CValue::get('id');
$field = CValue::get('field');

$object = null;

// Loads the expected Object
if (class_exists($class)) {
  $object = new $class;
  $object->load($id);
  if (!$object->canRead()) {
    CApp::rip();
  }
}

// Smarty template
$smarty = new CSmartyDP();

$smarty->assign('object', $object);
$smarty->assign('field', $field);

$smarty->display('inc_object_value.tpl');
