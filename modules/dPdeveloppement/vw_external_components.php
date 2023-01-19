<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$path = realpath(CAppUI::conf("dPdeveloppement external_repository_path"));

if (!$path || !is_dir($path)) {
  return;
}

$path = rtrim($path, "/\\");
$root = realpath(__DIR__."/../../");

$modules_dir = glob($path."/Modules/*", GLOB_ONLYDIR);
$styles_dir  = glob($path."/Styles/*",  GLOB_ONLYDIR);

$components = array(
  "module" => array(),
  "style"  => array(),
);

foreach ($modules_dir as $_dir) {
  $_name = basename($_dir);
  $components["module"][$_name] = array(
    "installed" => file_exists("$root/modules/$_name"),
  );
}

foreach ($styles_dir as $_dir) {
  $_name = basename($_dir);
  $components["style"][$_name] = array(
    "installed" => file_exists("$root/style/$_name"),
  );
}

$smarty = new CSmartyDP();
$smarty->assign("components", $components);
$smarty->display("vw_external_components.tpl");