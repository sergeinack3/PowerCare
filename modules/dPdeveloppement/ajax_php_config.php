<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$php_config = ini_get_all();
$php_config_important = array(
  "memory_limit",
  "default_socket_timeout",
  "max_execution_time",
  "mysql.connect_timeout",
  "session.gc_maxlifetime",
);
$php_config_tree = array(
  "general" => array()
);
foreach ($php_config as $key => $value) {
  $parts = explode(".", $key, 2);
  $value["user"] = $value["access"] & 1;
  if (count($parts) == 1) {
    $php_config_tree["general"][$key] = $value;
  }
  else {
    if (!isset($php_config_tree[$parts[0]])) {
      $php_config_tree[$parts[0]] = array();
    }

    $php_config_tree[$parts[0]][$key] = $value;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("php_config"          , $php_config_tree);
$smarty->assign("php_config_important", $php_config_important);
$smarty->display("inc_php_config.tpl");