<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CCorrespondantPatient;

CCanDo::checkAdmin();
$dryrun       = CView::post("dryrun", "bool default|1");
$force_update = CView::post("force_update", "bool default|0");
CView::checkin();
$file = isset($_FILES['import']) ? $_FILES['import'] : null;

$results = array();
$i       = 0;

if ($file && ($fp = fopen($file['tmp_name'], 'r'))) {
  // Object columns on the first line
  $cols = fgetcsv($fp, null, ";");

  // Each line
  while ($line = fgetcsv($fp, null, ";")) {
    $i++;
    if (!isset($line[0]) || $line[0] == "") {
      continue;
    }

    // Parsing
    $line                           = array_map("trim", $line);
    $line                           = array_map("addslashes", $line);
    $results[$i]["relation"]        = @CMbArray::get($line, 0);
    $results[$i]["relation_autre"]  = @CMbArray::get($line, 1);
    $results[$i]["nom"]             = @CMbArray::get($line, 2);
    $results[$i]["surnom"]          = @CMbArray::get($line, 3);
    $results[$i]["nom_jeune_fille"] = @CMbArray::get($line, 4);
    $results[$i]["prenom"]          = @CMbArray::get($line, 5);
    $results[$i]["naissance"]       = @CMbArray::get($line, 6);
    $results[$i]["sex"]             = @CMbArray::get($line, 7);
    $results[$i]["adresse"]         = @CMbArray::get($line, 8);
    $results[$i]["cp"]              = @CMbArray::get($line, 9);
    $results[$i]["ville"]           = @CMbArray::get($line, 10);
    $results[$i]["tel"]             = @CMbArray::get($line, 11);
    $results[$i]["mob"]             = @CMbArray::get($line, 12);
    $results[$i]["fax"]             = @CMbArray::get($line, 13);
    $results[$i]["urssaf"]          = @CMbArray::get($line, 14);
    $results[$i]["parente"]         = @CMbArray::get($line, 15);
    $results[$i]["parente_autre"]   = @CMbArray::get($line, 16);
    $results[$i]["email"]           = @CMbArray::get($line, 17);
    $results[$i]["remarques"]       = @CMbArray::get($line, 18);
    $results[$i]["ean"]             = @CMbArray::get($line, 19);
    $results[$i]["ean_base"]        = @CMbArray::get($line, 20);
    $results[$i]["type_pec"]        = @CMbArray::get($line, 21);
    $results[$i]["date_debut"]      = @CMbArray::get($line, 22);
    $results[$i]["date_fin"]        = @CMbArray::get($line, 23);
    $results[$i]["num_assure"]      = @CMbArray::get($line, 24);
    $results[$i]["employeur"]       = @CMbArray::get($line, 25);

    $results[$i]["error"] = 0;

    // Service
    $correspondant         = new CCorrespondantPatient();
    $correspondant->nom    = $results[$i]["nom"];
    $correspondant->prenom = $results[$i]["prenom"];
    if ($results[$i]["email"]) {
      $correspondant->email = $results[$i]["email"];
    }
    $correspondant->relation = $results[$i]["relation"];
    if ($results[$i]["date_debut"]) {
      $correspondant->date_debut = $results[$i]["date_debut"];
    }
    if ($force_update) {
      if ($results[$i]["naissance"]) {
        $correspondant->naissance = $results[$i]["naissance"];
      }
      if ($results[$i]["sex"]) {
        $correspondant->sex = $results[$i]["sex"];
      }
    }
    $correspondant->loadMatchingObject();
    if (!$correspondant->_id || $force_update) {
      $correspondant->relation_autre  = $results[$i]["relation_autre"];
      $correspondant->surnom          = $results[$i]["surnom"];
      $correspondant->nom_jeune_fille = $results[$i]["nom_jeune_fille"];
      $correspondant->naissance       = $results[$i]["naissance"];
      $correspondant->sex             = $results[$i]["sex"];
      $correspondant->adresse         = $results[$i]["adresse"];
      $correspondant->cp              = $results[$i]["cp"];
      $correspondant->ville           = $results[$i]["ville"];
      $correspondant->tel             = $results[$i]["tel"];
      $correspondant->mob             = $results[$i]["mob"];
      $correspondant->fax             = $results[$i]["fax"];
      $correspondant->urssaf          = $results[$i]["urssaf"];
      $correspondant->parente         = $results[$i]["parente"];
      $correspondant->parente_autre   = $results[$i]["parente_autre"];
      $correspondant->remarques       = $results[$i]["remarques"];
      $correspondant->ean             = $results[$i]["ean"];
      $correspondant->ean_base        = $results[$i]["ean_base"];
      $correspondant->type_pec        = $results[$i]["type_pec"];
      $correspondant->date_fin        = $results[$i]["date_fin"];
      $correspondant->num_assure      = $results[$i]["num_assure"];
      $correspondant->employeur       = $results[$i]["employeur"];
      // Dry run to check references
      if ($dryrun) {
        continue;
      }
      $title_exist = $correspondant->_id ? "modify" : "create";
      $msg         = $correspondant->store();
      if ($msg) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        $results[$i]["error"] = $msg;
        continue;
      }
      CAppUI::setMsg("CCorrespondantPatient-msg-" . $title_exist, UI_MSG_OK);
    }
    else {
      $results[$i]["error"] = "Ce correspondant est déjà présent";
    }
  }

  fclose($fp);
}

CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("results", $results);
$smarty->assign("dryrun", $dryrun);

$smarty->display("import_correspondants_patient_csv.tpl");
