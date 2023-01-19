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

CAppUI::stepAjax("Test de cr�ation de r�pertoire et d'un fichier dans ce r�pertoire", UI_MSG_WARNING);

// Cr�ation d'un r�pertoire

$directory_create = CMbPath::forceDir($dir);

if (!$directory_create) {
  CAppUI::stepAjax("Cr�ation de r�pertoire �chou�", UI_MSG_ERROR); CApp::rip();
}

CAppUI::stepAjax("Cr�ation de r�pertoire Ok", UI_MSG_OK);

// Cr�ation d'un fichier

$file_create = file_put_contents($dir . "/test_file", "a");

if (!$file_create) {
  CAppUI::stepAjax("Cr�ation de fichier �chou�", UI_MSG_ERROR);
  @unlink($dir);
  CApp::rip();
}

CAppUI::stepAjax("Cr�ation de fichier Ok", UI_MSG_OK);

// Suppression du fichier et du dossier
@unlink($dir . "/test_file");
@unlink($dir);

CAppUI::stepAjax("Fin de test de cr�ation de fichier", UI_MSG_WARNING);


