<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;

/**
 * Configuration de dompdf
 */
CApp::setMemoryLimit("256M");

// Le type de @media accepté par DOMPDF  
define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");

// Urls pour les images acceptées
define("DOMPDF_ENABLE_REMOTE", true);

// Backend de DOMPDF
define("DOMPDF_PDF_BACKEND", "CPDF");

// Police par défaut
define("DOMPDF_DEFAULT_FONT", "sans-serif");

// PHP inline desactivé
define("DOMPDF_ENABLE_PHP", false);

// Hauteur de ligne
define("DOMPDF_FONT_HEIGHT_RATIO", 1.0);

// Répertoire des fonts
$font_dir = CAppUI::conf("dPcompteRendu CCompteRendu font_dir");
if ($font_dir) {
  define("DOMPDF_FONT_DIR"  , $font_dir);
  define("DOMPDF_FONT_CACHE", $font_dir);
}