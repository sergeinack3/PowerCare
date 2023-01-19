<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;

CCanDo::checkAdmin();

$modules = array(
  "achilles",
  "ami",
  "axisante",
  "calystene",
  "crossway",
  "diane",
  "dpe",
  "dxcare",
  "hellodoc",
  "hypercare",
  "lifeline",
  "medicalNet",
  "medicawin",
  "medistory",
  "nova",
  "novaxel",
  "odyssee",
  "opesim",
  "osoft",
  "pckent",
  "primaPatient",
  "resurgences",
  "specilog",
  "surgica",
);

$modules_info = array();

foreach ($modules as $_module) {
  $modules_info[$_module] = array(
    "module_name" => $_module,
    "etat"        => "Absent",
    "doc_exist"   => 0
  );

  if (CModule::exists($_module)) {
    $modules_info[$_module]["etat"] = "Présent";
  }

  if (CModule::getInstalled($_module)) {
    $modules_info[$_module]["etat"] = "Installé";
  }

  if (CModule::getActive($_module)) {
    $modules_info[$_module]["etat"] = "Actif";
  }

  if (file_exists("modules/$_module/vw_doc.php")) {
    $modules_info[$_module]["doc_exist"] = 1;
  }
}

$smarty = new CSmartyDP();
$smarty->assign("modules", $modules_info);
$smarty->display("vw_import_tools.tpl");
