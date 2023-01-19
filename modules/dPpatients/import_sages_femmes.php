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
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Outil d'import de sage-femmes
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

$offset = 0;

$post = array(
  "typerubrique" => "1",
  "rubriqueid"   => "4",
  "elementid"    => "71",
  "offset"       => "0",
  "sessionid"    => "1962150",
  "rec9999"      => "recherche",
  "firstrec"     => "1002",
  "whichOne"     => "which_71",
  "rec1004"      => $departement,
  "rec1008"      => $departement,
  "rec1004text"  => $departement,
  "rec4"         => $departement,
  "rec8"         => $departement,
  "rec4list"     => $departement,
);

$url = "http://www.ordre-sages-femmes.fr/NET/fr/xslt.aspx";

$continue = 1;
$errors   = 0;
$count    = 0;

while ($continue) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Connection: close"));

  $result = curl_exec($ch);

  $result = str_replace("&nbsp;", " ", $result);
  $result = utf8_decode($result);
  $result = str_replace("\xA0", " ", $result);
  file_put_contents("tmp/rah.html", $result);

  $doc = new CMbXMLDocument("utf8");
  @$doc->loadHTML($result);

  $xpath = new CMbXPath($doc);

  $pages_remaining = $xpath->query("/html/body//span[@class='color_pagination']");

  if (!$pages_remaining->length) {
    $continue = 0;
  }

  curl_close($ch);

  $post["offset"] += 10;

  // Traitement des résultats de la page
  $sages_femmes = $xpath->query("/html/body//div[@class='result_ann_gch'] | /html/body//div[@class='result_ann_drt']");
  $count        += $sages_femmes->length;

  foreach ($sages_femmes as $_sage_femme) {
    $medecin  = new CMedecin();
    $identite = trim(str_replace("Madame", "", $xpath->queryTextNode("div[@class='top_res transparent']", $_sage_femme)));


    $first_space = strpos($identite, " ");

    $medecin->prenom = substr($identite, 0, $first_space);
    $medecin->nom    = substr($identite, $first_space + 1);

    $medecin->disciplines = "Sage-Femme";

    // Adresse, téléphone (fixe, portable, fax), email
    $infos = $xpath->query("div[@class='bas_res ']", $_sage_femme)->item(0);

    foreach ($infos->childNodes as $index => $_info) {
      switch ($index) {
        case "0":
          $medecin->adresse = $_info->nodeValue;
          break;
        case "1":
          continue 2;
        case "2":
          if ($_info->nodeName == "#text") {
            $medecin->adresse .= "\n" . $_info->nodeValue;
          }
          break;
        case "3":
          // Adresse
          if ($_info->nodeName == "#text") {
            $first_space    = strpos($_info->nodeValue, " ");
            $medecin->cp    = substr($_info->nodeValue, 0, $first_space);
            $medecin->ville = substr($_info->nodeValue, $first_space + 1);
          }
          break;
        case "4":
          if ($_info->nodeName == "#text") {
            // Adresse
            $first_space    = strpos($_info->nodeValue, " ");
            $medecin->cp    = substr($_info->nodeValue, 0, $first_space);
            $medecin->ville = substr($_info->nodeValue, $first_space + 1);
          }
          elseif ($_info->nodeName == "div") {
            // Téléphone
            foreach (explode("\n", $_info->nodeValue) as $_ligne_tel) {
              $_ligne_tel = trim($_ligne_tel);
              if (!$_ligne_tel) {
                continue;
              }

              $parts = explode(":", $_ligne_tel);
              $tel   = str_replace(" ", "", $parts[1]);
              if (preg_match("/port/", $_ligne_tel)) {
                $medecin->portable = $tel;
              }
              elseif (preg_match("/Fax/", $_ligne_tel)) {
                $medecin->fax = $tel;
              }
              else {
                $medecin->tel = $tel;
              }
            }
          }
          break;
        case "5":
          // Téléphone
          if ($_info->nodeName == "div") {
            foreach (explode("\n", $_info->nodeValue) as $_ligne_tel) {
              $_ligne_tel = trim($_ligne_tel);
              if (!$_ligne_tel) {
                continue;
              }

              $parts = explode(":", $_ligne_tel);
              $tel   = str_replace(" ", "", $parts[1]);
              if (preg_match("/port/", $_ligne_tel)) {
                $medecin->portable = $tel;
              }
              elseif (preg_match("/Fax/", $_ligne_tel)) {
                $medecin->fax = $tel;
              }
              else {
                $medecin->tel = $tel;
              }
            }
          }
          elseif ($_info->nodeName == "a") {
            // Email
            $medecin->email = trim($_info->nodeValue);
          }
          break;
        case "6":
          // Email
          if ($_info->nodeName == "a") {
            $medecin->email = trim($_info->nodeValue);
          }
      }
    }

    $msg = $medecin->store();

    if ($msg) {
      CApp::log('in boucle', $errors);
      $errors++;
      CAppUI::stepAjax($msg . "\n" . "$medecin->nom, $medecin->prenom $medecin->cp $medecin->ville", UI_MSG_ERROR);
    }
  }
}
CApp::log('after', $errors);
if ($errors == 0) {
  CAppUI::stepAjax("$count/$count sages-femmes importées - Département $departement", UI_MSG_OK);
}
else {
  $sub = $count - $errors;
  CAppUI::stepAjax("$sub/$count sages-femmes importées - Département $departement", UI_MSG_WARNING);
}
