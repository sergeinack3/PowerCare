<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Hl7\CHL7v2;

CCanDo::checkRead();

$version = CValue::getOrSession("version", "hl7v2_5");

if (preg_match('/^([a-z]{3})_(v[\d_]+)$/', $version, $matches)) {
  $version_dir = "/extensions/$matches[1]/$matches[2]/";
}
else {
  $version_dir = "/$version/";
}

$schema_path = CHL7v2::LIB_HL7.$version_dir;

$schemas = array(
  "message" => null,
  "segment" => null,
  "composite" => null,
);

foreach ($schemas as $type => $composite) {
  $paths = glob($schema_path."$type*.xml");
  
  foreach ($paths as $path) {
    if (preg_match('/hl7v3/', $path)) {
      continue;
    }

    preg_match("/$type(.+)\.xml$/", $path, $matches);
    $name = $matches[1];
    
    if ($type == "message" && strlen($name) > 3) {
      $prefix = substr($name, 0, 3);
      if (!isset($schemas[$type][$prefix])) {
        $schemas[$type][$prefix] = array();
      }
      $schemas[$type][$prefix][substr($name, 3)] = $path;
    }
    else {
      $schemas[$type][$name] = $path;
    }
  }
}

$versions = array(
  "int" => array(),
  "ext" => array(),
);

$versions_int_paths = glob(CHL7v2::LIB_HL7."/hl7v2*");
foreach ($versions_int_paths as $path) {
  $versions["int"][] = basename($path);
}

$versions_int_paths = glob(CHL7v2::LIB_HL7."/extensions/*/*");
foreach ($versions_int_paths as $path) {
  $_version = basename($path);
  $_language = basename(dirname($path));
  $versions["ext"][] = "{$_language}_{$_version}";
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("schemas", $schemas);
$smarty->assign("version", $version);
$smarty->assign("versions", $versions);
$smarty->display("vw_hl7v2_schemas.tpl");