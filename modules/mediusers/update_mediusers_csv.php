<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;

/**
 * Update mediusers
 */
CCanDo::checkAdmin();

$file    = isset($_FILES['import']) ? $_FILES['import'] : null;

$results = array();
$i       = 0;

if ($file && ($fp = fopen($file['tmp_name'], 'r'))) {
  // Object columns on the first line
  $cols = fgetcsv($fp, null, ";");

  // Each line
  while ($line = fgetcsv($fp, null, ";")) {
    if (!isset($line[0]) || $line[0] == "") {
      continue;
    }
    
    $results[$i]["error"] = 0;
    
    // Parsing
    $results[$i]["lastname"]      = addslashes(trim($line[0]));
    $results[$i]["firstname"]     = addslashes(trim($line[1]));
    $results[$i]["adeli"]         = addslashes(trim($line[2]));
    
    if (!$results[$i]["lastname"] && $results[$i]["firstname"]) {
      continue;
    }
    
    $user = new CUser();
    $user->user_last_name  = $results[$i]["lastname"];
    $user->user_first_name = $results[$i]["firstname"];
    
    $count = $user->countMatchingList();
    
    if ($count == "0") {
      $results[$i]["error"] = "L'utilisateur n'a pas été retrouvé dans Mediboard";
      $i++;
      continue;
    }
    elseif ($count > 1) {
      $results[$i]["error"] = "Plusieurs utilisateurs correspondent à cette recherche";
      $i++;
      continue;
    }
    
    $user->loadMatchingObject();
    $mediuser = $user->loadRefMediuser();
    $mediuser->adeli = $results[$i]["adeli"];
    if ($msg = $mediuser->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
      $results[$i]["error"] = $msg;
      $i++;
      continue;
    }

    $i++;
  }
}

CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("results", $results);
$smarty->display("update_mediusers_csv.tpl");
