<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CMbObject;
use Ox\Core\CValue;

$guid    = CValue::get("guid");
$field   = CValue::get("field");
$options = CValue::get("options");

$object = CMbObject::loadFromGuid($guid);

if (!$object || !$object->canRead()) {
  return;
}

$object->loadView();

$result = "";

if ($field) {
  if ($options) {
    $result = $object->getFormattedValue($field, $options);
  }
  else {
    $result = $object->$field;
  }
}
else {
  $result = get_object_vars($object);
}

CApp::json($result);