<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassField;

$ex_class_field_id = CValue::post("ex_class_field_id");

$ex_class_field = new CExClassField();
$ex_class_field->load($ex_class_field_id);

if (empty($_POST["value"])) {
  $_POST["value"] = $_POST[$ex_class_field->name];
}

$do = new CDoObjectAddEdit("CExClassFieldPredicate");
$do->doIt();
