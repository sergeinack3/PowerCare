<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Outil d'import de kinés
 */
CCAnDo::checkAdmin();

CApp::setTimeLimit(150);

if (!class_exists("DOMDocument")) {
  trigger_error("sorry, DOMDocument is needed");

  return;
}

if (null == $pass = CValue::get("pass")) {
  CAppUI::stepAjax("Fonctionnalité désactivée car trop instable.", UI_MSG_WARNING);

  return;
}

if (md5($pass) != "aa450aff6d0f4974711ff4c5536ed4cb") {
  CAppUI::stepAjax("Mot de passe incorrect.\nAttention, fonctionnalité à utiliser avec une extrême prudence", UI_MSG_ERROR);
}

$departement = CValue::get("departement");

$url = "http://www.ordremk.fr/searchproxy.php?format=json&name=%25%25%25&zip=";

$continue = 1;
$errors   = 0;
$count    = 0;
$step     = 0;

while ($continue) {
  $ch          = curl_init();
  $url_request = $url . $departement . str_pad($step, "3", "0", STR_PAD_LEFT);

  $result = file_get_contents($url_request);
  $result = json_decode($result, true);
  $result = $result["mks"];
  $result = array_map_recursive("utf8_decode", $result);

  // Traitement des résultats de la page
  $continue = $step <= 999;

  $step++;
  $count += count($result);

  foreach ($result as $_result) {
    $medecin              = new CMedecin();
    $medecin->disciplines = "Kinésitherapeute";
    $medecin->nom         = $_result["nom"];
    $medecin->prenom      = $_result["prenom"];
    $medecin->adresse     = $_result["adresse"];
    if ($_result["adresse_suite"] != "") {
      $medecin->adresse .= "\n" . $_result["adresse_suite"];
    }
    $medecin->cp    = $_result["zip"];
    $medecin->ville = $_result["ville"];
    $msg            = $medecin->store();
    if ($msg) {
      $errors++;
      CAppUI::stepAjax($msg . "\n" . "$medecin->nom, $medecin->prenom $medecin->cp $medecin->ville", UI_MSG_ERROR);
    }
  }
}

if ($errors == 0) {
  CAppUI::stepAjax("$count/$count kinés importés - Département $departement", UI_MSG_OK);
}
else {
  $sub = $count - $errors;
  CAppUI::stepAjax("$sub/$count kinés importés - Département $departement", UI_MSG_WARNING);
}