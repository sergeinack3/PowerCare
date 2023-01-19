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
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Import idex users
 */
CCanDo::checkAdmin();

$file    = isset($_FILES['import']) ? $_FILES['import'] : null;

$results = array();
$i       = 0;

if (!CMediusers::getTagMediusers()) {
  CAppUI::stepAjax("Aucun tag de défini pour les mediusers", UI_MSG_ERROR);
}

$group_id = CGroups::loadCurrent()->_id;

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
    $results[$i]["adeli"]     = addslashes(trim($line[0]));
    $results[$i]["idex"]      = addslashes(trim($line[1]));
    $results[$i]["lastname"]  = isset($line[2]) ? addslashes(trim($line[2])) : null;
    $results[$i]["firstname"] = isset($line[3]) ? addslashes(trim($line[3])) : null;

    $user = new CUser();
    $ljoin["users_mediboard"]     = "users_mediboard.user_id = users.user_id";
    $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

    $where = array(
      "users_mediboard.adeli"    => " = '".$results[$i]["adeli"]."'",
      "functions_mediboard.group_id" => " = '$group_id'"
    );

    $user = new CUser();
    $count = $user->countList($where, null, $ljoin);
    
    if ($count == 0) {
      $results[$i]["error"] = "L'utilisateur n'a pas été retrouvé dans Mediboard";
      $i++;
      continue;
    }
    elseif ($count > 1) {
      $results[$i]["error"] = "Plusieurs utilisateurs correspondent à cette recherche";
      $i++;
      continue;
    }

    $user->loadObject($where, null, null, $ljoin);

    $mediuser = $user->loadRefMediuser();
    
    // Recherche pas nom/prenom si pas de code ADELI
    if (!$mediuser->_id) {
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
    }
    
    $idex = CIdSante400::getMatch($mediuser->_class, CMediusers::getTagMediusers(), null, $mediuser->_id);
    if ($idex->_id && ($idex->id400 != $results[$i]["idex"])) {
      $results[$i]["error"] = "L'utilisateur possède déjà un identifiant ('$idex->id400') externe dans Mediboard";
      $i++;
      continue;
    }
    
    if ($idex->_id) {
      $i++;
      continue;
    }
    
    $idex->id400 = $results[$i]["idex"];
    if ($msg = $idex->store()) {
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
$smarty->display("update_idex_csv.tpl");
