<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;

CAppUI::stepAjax("Outil expérimental, commenter cette ligne pour l'utiliser", UI_MSG_ERROR);

CCanDo::checkAdmin();

$name   = CValue::post("name");
$type   = CValue::post("type");
$action = CValue::post("action");

$path = realpath(CAppUI::conf("dPdeveloppement external_repository_path"));

if (!$path || !is_dir($path)) {
  return;
}

$name = preg_replace('/[^\w_]/', '', $name);

$path = rtrim($path, "/\\");
$root = realpath(__DIR__."/../../../");

switch ($type) {
  case "module":
    $link   = "$root/modules/$name";
    $target = "$path/Modules/$name";
    break;

  case "style":
    $link   = "$root/style/$name";
    $target = "$path/Styles/$name";
    break;

  default:
    CApp::rip();
}

if ($action === "remove") {
  if (!is_link($link)) {
    CAppUI::stepAjax("Le dossier '%s' n'est pas un lien", UI_MSG_ERROR, $link);
    return;
  }

  if (stripos(PHP_OS, "WIN") === 0) {
    // http://stackoverflow.com/questions/18262555/remove-a-symlink-with-php-on-windows
    $result = rmdir($link);
  }
  else {
    $result = unlink($link);
  }

  if ($result) {
    CAppUI::stepAjax("Lien vers le %s %s supprimé", UI_MSG_OK, $type, $name);
  }
  else {
    CAppUI::stepAjax("Erreur lors de la suppression du lien vers le %s %s", UI_MSG_WARNING, $type, $name);
  }
}
else {
  if (file_exists($link)) {
    CAppUI::stepAjax("Le dossier '%s' existe déjà", UI_MSG_ERROR, $link);
    return;
  }

  if (symlink($target, $link)) {
    CAppUI::stepAjax("Lien vers le %s %s créé", UI_MSG_OK, $type, $name);
  }
  else {
    CAppUI::stepAjax("Erreur lors de la création du lien vers le %s %s", UI_MSG_WARNING, $type, $name);
  }
}
