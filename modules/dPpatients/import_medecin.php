<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $m;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbPath;
use Ox\Core\CMbXPath;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CMedecin;

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

// Chrono start
$chrono = new Chronometer();
$chrono->start();

$segment = CValue::get("segment", 1000);
$step    = CValue::get("step", 1);
$from    = $step > 1 ? 100 + $segment * ($step - 2) : 0;
$to      = $step > 1 ? 100 + ($step - 1) * $segment : 100;

$padded  = str_pad($step, "3", "0", STR_PAD_LEFT);
$htmpath = "tmp/ordre/medecin$padded.htm";
$xmlpath = "tmp/ordre/medecin$padded.xml";
$csvpath = "tmp/ordre/medecin$padded.csv";
CMbPath::forceDir(dirname($htmpath));

$mode = CValue::get("mode");

// Step 1: Emulates an HTTP request
if ($mode == "get") {
  $departement = CValue::get("departement");
  $cookiepath  = CAppUI::getTmpPath("cookie.txt");
  $page        = $step - 1;
  $url_ch1     = "http://www.conseil-national.medecin.fr/annuaire";
  $url_ch2     = "http://www.conseil-national.medecin.fr/annuaire/resultats?page=$page";

  $post = array(
    "sexe"          => 3,
    "departement"   => $departement,
    "op"            => "Recherche",
    "form_build_id" => "form-c2b45a67c53fdd389338ffee58d2c1c2",
    "form_id"       => "cn_search_med_advanced_form");

  $ch  = curl_init();
  $ch2 = curl_init();

  curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiepath);
  curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiepath);
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_URL, $url_ch1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Connection:: keep-alive"));

  curl_setopt($ch2, CURLOPT_COOKIEJAR, $cookiepath);
  curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookiepath);
  curl_setopt($ch2, CURLOPT_VERBOSE, 1);
  curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch2, CURLOPT_URL, $url_ch2);
  curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);

  // La recherche a un redirect. Il faut faire une sous requête pour obtenir la bonne page.
  $mh = curl_multi_init();

  curl_multi_add_handle($mh, $ch);
  curl_multi_add_handle($mh, $ch2);

  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while ($running > 0);

  $result = curl_multi_getcontent($ch2);

  curl_multi_remove_handle($mh, $ch);
  curl_multi_remove_handle($mh, $ch2);
  curl_multi_close($mh);

  file_put_contents($htmpath, $result);

  // -- Step: Get data from html file
  $str = @file_get_contents($htmpath);
  if (!$str) {
    // Création du template
    $smarty = new CSmartyDP();

    $smarty->assign("end_of_process", true);
    $smarty->display("import_medecin.tpl");

    return;
  }
}

// Step 2: Parse XML
$xpath_screwed = false;
$last_page     = 0;

if (in_array($mode, array("get", "xml"))) {

  // Open CSV File
  $csvfile = fopen($csvpath, "w");
  $medecin = new CMedecin();
  fputcsv($csvfile, array_keys($medecin->getCSVFields()));

  // Purge HTML
  if (null == $html = file_get_contents($htmpath)) {
    CAppUI::stepAjax("Fichier '$htmpath' non disponible", UI_MSG_ERROR);
  }

  // Small adjustments for line delimitation:  <br/> to \n
  $html = str_replace("<br>", "\n", $html);


  // Prepare the document
  $doc = @(new DOMDocument())->loadHTML($html);
  file_put_contents($xmlpath, isset($doc) ? $doc->saveXML() : null);

  $xpath = new CMbXPath($doc);

  // Check last page
  $query = "/html/body//li[@class='pager-current last']";
  // Two links to each page, so check if there are 2 elements pointing the last page.
  $last_page = $xpath->query($query)->length == 2;

  $query = "/html/body//table[@id]/tr";
  /** @var CMedecin[] $medecins */
  $medecins = array();
  foreach ($xpath->query($query) as $key => $nodeMainTr) {
    $ndx = intval($key / 4);
    $mod = intval($key % 4);

    if ($nodeMainTr->nodeName != "tr") {
      trigger_error("Not a main &lt;tr&gt; DOM Node", E_USER_WARNING);
      $xpath_screwed = true;
      break;
    }

    // Création du médecin
    if (!array_key_exists($ndx, $medecins)) {
      $medecins[$ndx] = new CMedecin();
    }

    $medecin       =& $medecins[$ndx];
    $medecin->type = "medecin";

    $xpath2 = new CMbXPath($doc);
    switch ($mod) {
      case 0:
        // Nom du médecin
        $query      = "td[2]/span[1]";
        $nom_prenom = $xpath2->queryTextNode($query, $nodeMainTr);

        preg_match('/^\s*(.+)\s+([^\s]+)\s*$/', $nom_prenom, $matches);

        $medecin->nom    = $matches[1];
        $medecin->prenom = $matches[2];

        // RPPS
        $query         = "td[2]/strong[1]";
        $medecin->rpps = $xpath2->queryTextNode($query, $nodeMainTr);

        break;

      case 1:
        // Disciplines qualifiantes
        // Le champ discipline exercée peut ne pas être renseigné.
        $query                = "td[2]/strong";
        $medecin->disciplines = $xpath2->query($query, $nodeMainTr)->item(0)->nextSibling ?
          $xpath2->query($query, $nodeMainTr)->item(0)->nextSibling->nodeValue : "";

        break;

      case 2:
        $query    = "td[2]";
        $infos    = $xpath2->query($query, $nodeMainTr);
        $td       = $infos->item(0);
        $child    = $td->firstChild;
        $cp_ville = $td->lastChild;

        // Adresse
        $medecin->adresse = "";
        while ($child !== $cp_ville) {
          if ($child->nodeName === "br") {
            $medecin->adresse .= "\n";
          }
          else {
            $medecin->adresse .= $child->nodeValue;
          }
          $child = $child->nextSibling;
        }

        // Code postal - Ville
        $cp_ville       = $cp_ville->nodeValue;
        $first_space    = strpos($cp_ville, " ");
        $medecin->cp    = substr($cp_ville, 0, $first_space);
        $medecin->ville = substr($cp_ville, $first_space);

        // Disciplines complémentaires - Téléphone - Fax
        $query = "td[3]/strong";

        foreach ($xpath2->query($query, $nodeMainTr) as $node) {
          switch (trim($node->nodeValue)) {
            case "Disciplines complementaires d'exercice :":
              $child = $node->nextSibling;
              // Tester la présence du nextSibling (pas de téléphone ni de fax)
              while ($child->nodeName !== "strong" && $child->nextSibling) {
                if ($child->nodeName === "br") {
                  $medecin->complementaires .= "\n";
                }
                else {
                  $medecin->complementaires .= $child->nodeValue;
                }
                $child = $child->nextSibling;
              }
              break;
            case "Tel:":
              $medecin->tel = trim(str_replace(".", " ", $node->nextSibling->nodeValue));
              break;
            case "Fax:":
              $medecin->fax = trim(str_replace(".", " ", $node->nextSibling->nodeValue));
          }
        }

        fputcsv($csvfile, $medecin->getCSVFields());
        break;

      case 3:
        // Empty tr
    }
  }

  fclose($csvfile);
}

// Step 3: Store from CSV summary
$errors  = 0;
$updates = 0;

// Open CSV File
if (null == $csvfile = @fopen($csvpath, "r")) {
  CAppUI::stepAjax("Fichier '$csvpath' non disponible", UI_MSG_ERROR);
}
$cols = fgetcsv($csvfile);

// Each line
$medecins    = array();
$mode_import = CValue::get("mode_import");

while ($line = fgetcsv($csvfile)) {
  // Load from CSV
  $medecin = new CMedecin;
  foreach ($cols as $index => $field) {
    $medecin->$field = $line[$index];
  }

  // Recherche des siblings
  $siblings = $medecin->loadExactSiblings();

  if ($medecin->_has_siblings = count($siblings)) {
    $sibling = reset($siblings);
    switch ($mode_import) {
      case "comp":
        $medecin->_id = $sibling->_id;
        break;
      case "rpps":
        $rpps          = $medecin->rpps;
        $medecin       = $sibling;
        $medecin->rpps = $rpps;
    }
    $updates++;
  }

  // Sauvegarde
  $medecin->repair();
  if ($msg = $medecin->store()) {
    trigger_error("Error storing $medecin->nom $medecin->prenom ($medecin->cp) : $msg", E_USER_WARNING);
    $errors++;
  }

  $medecin->updateFormFields();
  $medecins[] = $medecin;
}

$chrono->stop();

CAppUI::stepAjax("Etape $step \n$errors erreurs d'enregistrements", $errors ? UI_MSG_OK : UI_MSG_ALERT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("verbose", CValue::get("verbose"));

$smarty->assign("xpath_screwed", $xpath_screwed);
$smarty->assign("step", $step);
$smarty->assign("from", $from);
$smarty->assign("to", $to);
$smarty->assign("medecins", $medecins);
$smarty->assign("chrono", $chrono);
$smarty->assign("updates", $updates);
$smarty->assign("errors", $errors);
$smarty->assign("last_page", $last_page);

$smarty->display("import_medecin.tpl");
