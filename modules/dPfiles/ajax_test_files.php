<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbPath;
use Ox\Mediboard\Files\CFile;

$dir = CFile::getDirectory() . "/test";

CAppUI::stepAjax("Test de création de répertoire et d'un fichier dans ce répertoire", UI_MSG_WARNING);

// Création d'un répertoire

$directory_create = CMbPath::forceDir($dir);

if (!$directory_create) {
  CAppUI::stepAjax("Création de répertoire échoué", UI_MSG_ERROR); CApp::rip();
}

CAppUI::stepAjax("Création de répertoire Ok", UI_MSG_OK);

// Création d'un fichier

$file_create = file_put_contents($dir . "/test_file", "a");

if (!$file_create) {
  CAppUI::stepAjax("Création de fichier échoué", UI_MSG_ERROR);
  @unlink($dir);
  CApp::rip();
}

CAppUI::stepAjax("Création de fichier Ok", UI_MSG_OK);

// Suppression du fichier et du dossier
@unlink($dir . "/test_file");
@unlink($dir);

CAppUI::stepAjax("Fin de test de création de fichier", UI_MSG_WARNING);


