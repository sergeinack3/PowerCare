<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;

CCanDo::checkAdmin();

$path = CAppUI::getTmpPath("pmsi");
$path .= "/cim10/LIBCIM10.TXT";
$result = array();
if (file_exists($path)) {
  if (!$fp = fopen("$path", "r+")) {
    CAppUI::displayAjaxMsg("Echec de l'ouverture du fichier LIBCIM10.txt", UI_MSG_WARNING);
  }
  else {
    while (!feof($fp)) {

      // On récupère une ligne
      $ligne = fgets($fp);

      if ($ligne) {
        $ligne = utf8_encode($ligne);
        $_ligne = explode('|', $ligne);
        $_ligne = array_map("trim", $_ligne);
        $result [] = implode(";", $_ligne)."\n";
      }
    }
    fclose($fp); // On ferme le fichier
  }

$path = CAppUI::getTmpPath("pmsi");
    $fic =  $path."/cim10/MCCIM10.csv";
    $fichier = fopen($fic, "w+");
    fwrite($fichier, implode("", $result));
    fclose($fichier);

CApp::rip();















}