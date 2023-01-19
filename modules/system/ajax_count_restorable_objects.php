<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkAdmin();

$user_id      = CValue::get("user_id");
$date         = CValue::get("date");
$object_class = CValue::get("object_class");
$fields       = CValue::get("fields");

$user_log = new CUserLog();

$where = array(
  "object_class" => "= '$object_class'"
);

if ($user_id) {
  $where["user_id"] = " = '$user_id'";
}

if ($date) {
  $where["date"] = ">= '$date'";
}

$where["type"] = "= 'store'";

if ($fields) {
  $whereField = array();
  foreach ($fields as $_field) {
    $whereField[] = "
      fields LIKE '$_field %' OR 
      fields LIKE '% $_field %' OR 
      fields LIKE '% $_field' OR 
      fields LIKE '$_field'";
  }
  $where[] = implode(" OR ", $whereField);
}

$count = $user_log->countListGroupBy($where, 'date ASC', 'object_id');
echo $count;
CApp::rip();
