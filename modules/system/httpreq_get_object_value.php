<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;

CCanDo::checkRead();

$class           = CValue::get('class');
$id              = CValue::get('id');
$field           = CValue::get('field');
$content_type    = CValue::get('content_type');
$formatted_value = CValue::get('formatted_value');

// Loads the expected Object
if (class_exists($class)) {
  /** @var CMbObject $object */
  $object = new $class;
  $object->load($id);
}

if ($content_type) {
  header("Content-Type: $content_type");
}

if ($formatted_value) {
  echo $object->getFormattedValue($field);
}
else {
  echo $object->$field;
}


