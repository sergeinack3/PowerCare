<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * dPbloc
 */
CCanDo::checkAdmin();

$file = isset($_FILES['import']) ? $_FILES['import'] : null;

$results = array();
$i = 0;

if ($file && ($fp = fopen($file['tmp_name'], 'r'))) {
  // Object columns on the first line
  $cols = fgetcsv($fp, null, ";");

  // Each line
  while ($line = fgetcsv($fp, null, ";")) {
    if (!isset($line[0]) || $line[0] == "") {
      continue;
    }
    
    // Parsing
    $results[$i]["bloc"]    = addslashes(trim($line[0]));
    $results[$i]["nom"]     = addslashes(trim($line[1]));
    
    $results[$i]["error"] = 0;
    
    // Bloc
    $bloc = new CBlocOperatoire();
    $bloc->nom      = $results[$i]["bloc"];
    $bloc->group_id = CGroups::loadCurrent()->_id;
    $bloc->loadMatchingObject();
    if (!$bloc->_id) {
      $msg = $bloc->store();
      if ($msg) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
        $results[$i]["error"] = $msg;
        $i++;
        continue;
      }
      CAppUI::setMsg("Bloc créé", UI_MSG_OK);
    }
    
    // Salle
    $salle = new CSalle();
    $salle->nom = $results[$i]["nom"];
    $salle->bloc_id = $bloc->_id;
    $salle->loadMatchingObject();
    if ($salle->_id) {
      $msg = "Salle existante";
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      $results[$i]["error"] = $msg;
      $i++;
      continue;
    }
    $salle->stats = 1;
    $salle->dh    = 0;
    $msg = $salle->store();
    if ($msg) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      $results[$i]["error"] = $msg;
      $i++;
      continue;
    }
    CAppUI::setMsg("Salle créée", UI_MSG_OK);
    
    $i++;
  }
  fclose($fp);
}

CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("results", $results);

$smarty->display("salles_import_csv.tpl");
