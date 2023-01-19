<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CLibelleOp;

CCanDo::checkAdmin();

CApp::setTimeLimit(240);

$file = CValue::files("import");
$dryrun = CValue::post("dryrun");

$results = array();
$unfound = array();
$i = 0;

if ($file && ($fp = fopen($file['tmp_name'], 'r'))) {
  // Object columns on the first line
  fgetcsv($fp, null, ";");
  $cols = fgetcsv($fp, null, ";");

  // Each line
  while ($line = fgetcsv($fp, null, ";")) {
    $i++;

    // Skip empty lines
    if (!isset($line[0]) || $line[0] == "") {
      continue;
    }

    // Parsing
    $line = array_map("trim"      , $line);
    $line = array_map("addslashes", $line);
    $results[$i]["statut"]    = CMbArray::get($line,  0);
    $results[$i]["nom"]       = CMbArray::get($line,  1);
    $results[$i]["date_debut"]= CMbArray::get($line,  2);
    $results[$i]["date_fin"]  = CMbArray::get($line,  3);
    $results[$i]["services"]  = CMbArray::get($line,  4);
    $results[$i]["mots_cles"] = CMbArray::get($line,  5);
    $results[$i]["numero"]    = CMbArray::get($line,  6);
    $results[$i]["version"]   = CMbArray::get($line,  7);

    $results[$i]["errors"] = array();

    if (!$results[$i]["nom"]) {
      $results[$i]["errors"][] = "Nom du libellé pas défini";
    }
    else {
      // Libellé
      $libelle = new CLibelleOp();
      if ($results[$i]["statut"] == "Validé") {
        $results[$i]["statut"] = "valide";
      }
      if ($results[$i]["date_debut"]) {
        $results[$i]["date_debut"] = CMbDT::dateTime($results[$i]["date_debut"]);
      }
      if ($results[$i]["date_fin"]) {
        $results[$i]["date_fin"]   = CMbDT::dateTime($results[$i]["date_fin"]);
      }

      $libelle->group_id = CGroups::loadCurrent()->_id;
      $libelle->statut    = $results[$i]["statut"];
      $libelle->nom       = $results[$i]["nom"];
      $libelle->date_debut= $results[$i]["date_debut"];
      $libelle->date_fin  = $results[$i]["date_fin"];
      $libelle->services  = $results[$i]["services"];
      $libelle->mots_cles = $results[$i]["mots_cles"];
      $libelle->numero    = $results[$i]["numero"];
      $libelle->version   = $results[$i]["version"];

      // No store on errors
      if (count($results[$i]["errors"])) {
        continue;
      }

      // Dry run to check references
      if ($dryrun) {
        continue;
      }

      // Creation
      $existing = $libelle->_id;
      if ($msg = $libelle->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        $results[$i]["errors"][] = $msg;
        continue;
      }
    }
    CAppUI::setMsg($existing ? "CLibelleOp-msg-modify" : "CLibelleOp-msg-create", UI_MSG_OK);
  }

  fclose($fp);
}

CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("results", $results);

$smarty->display("libelle_import_csv");
