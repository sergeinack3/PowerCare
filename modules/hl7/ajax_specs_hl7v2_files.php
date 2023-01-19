<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbPath;
use Ox\Core\CValue;

CCanDo::checkRead();

$version = CValue::get("version");
$check   = CValue::get("check");
$extract = CValue::get("extract");

$racine  = "modules/hl7/resources";
$lib_hl7 = "lib/hl7";

$specs_name     = "hl7v".str_replace(".", "_", $version);
$destinationDir = "$lib_hl7/$specs_name";
$archivePath    = "$racine/$specs_name.zip";
  
if ($extract) {
  if (file_exists($destinationDir)) {
    CMbPath::remove($destinationDir) ? 
      CAppUI::stepAjax("Suppression de $destinationDir") : 
      CAppUI::stepAjax("Impossible de supprimer le dossier $destinationDir", UI_MSG_ERROR);
  }
  if (false != $nbFiles = CMbPath::extract($archivePath, $destinationDir)) {
    CAppUI::stepAjax("Extraction de $nbFiles fichiers pour $specs_name");
    $check = true;
  } else {
    CAppUI::stepAjax("Impossible d'extraire l'archive $schemaFile", UI_MSG_ERROR);
  }
}

if ($check) {
  $status = 0;
  if (file_exists($destinationDir)) {
    $status = 1; 
  }
  
  $status ? CAppUI::stepAjax("Fichiers présents") : CAppUI::stepAjax("Fichiers manquants", UI_MSG_ERROR);
}

