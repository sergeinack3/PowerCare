<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;

$user = CUser::get();

$ds = CSQLDataSource::get("std");

$type         = CValue::get("type");
$mode         = CValue::get("mode", "stats");
$order        = CValue::get("order", "taux");
$chir         = CValue::get("chir");
$anesth       = CValue::get("anesth");
$object_class = CValue::get("object_class");

$profiles = array (
  "chir"   => $chir,
  "anesth" => $anesth,
  "user"   => $user->_id,
);

if ($profiles["user"] == $profiles["anesth"] || $profiles["user"] == $profiles["chir"]) {
  unset($profiles["user"]);
}

if (!$profiles["anesth"]) {
  unset($profiles["anesth"]);
}


$listByProfile = array();
$users = array();
foreach ($profiles as $profile => $_user_id) {
  // Chargement du user du profile
  $_user = new CMediusers();
  $_user->load($_user_id);
  $users[$profile] = $_user;
  
  $list = array();
  if ($type == "ccam") {
    /**
     * Favoris
     */
    if ($mode == "favoris") {
      $condition = "favoris_user = '$_user_id'";
      if ($object_class != "") {
        $condition .= " AND object_class = '$object_class'";
      }

      $sql = "select favoris_code
          from ccamfavoris
          where $condition
          group by favoris_code
          order by favoris_code";
      $codes = $ds->loadlist($sql);

      foreach ($codes as $key => $value) {
        // Attention à bien cloner le code CCAM car on rajoute une champ à la volée
        $code = CDatedCodeCCAM::get($value["favoris_code"]);
        if (
          CAppUI::pref('actes_comp_supp_favoris', 0) ||
          (!CAppUI::pref('actes_comp_supp_favoris', 0) && !in_array($code->chapitres[0]['db'], array('18.', '19.')))
        ) {
          $code->occ = "0";
          $list[$value["favoris_code"]] = $code;
        }
      }

      sort($list);
    }


    /**
     *  Statistiques
     */
    if ($mode == "stats") {

      // Appel de la fonction listant les codes les plus utilisés pour un praticien
      $actes = new CActeCCAM();
      $codes = $actes->getFavoris($_user_id, $object_class);

      foreach ($codes as $key => $value) {
        // Attention à bien cloner le code CCAM car on rajoute une champ à la volée
        $code = CDatedCodeCCAM::get($value["code_acte"]);
        if (
          CAppUI::pref('actes_comp_supp_favoris', 0) ||
          (!CAppUI::pref('actes_comp_supp_favoris', 0) && !in_array($code->chapitres[0]['db'], array('18.', '19.')))
        ) {
          $code->occ = $value["nb_acte"];
          $list[$value["code_acte"]] = $code;
        }
      }

      if ($order == "alpha") {
        sort($list);
      }
    }
  }

  if ($type=="cim10") {
    /**
     * Favoris
     */
    if ($mode == "favoris") {
      $sql = "select favoris_code
          from cim10favoris
          where favoris_user = '$_user_id'
          order by favoris_code";
      $codes = $ds->loadlist($sql);

      foreach ($codes as $key => $value) {
        $list[$value["favoris_code"]] = CCodeCIM10::get($value["favoris_code"]);
        $list[$value["favoris_code"]]->occurrences = "0";
      }
    }

    /**
     *  Statistiques
     */
    if ($mode == "stats") {
      // Chargement des codes cim les plus utilsé par le praticien $chir
      $code = new CCodeCIM10();

      $sql = "SELECT DP, count(DP) as nb_code
              FROM `sejour`
              WHERE sejour.praticien_id = '$_user_id'
              AND DP IS NOT NULL
              AND DP != ''
              GROUP BY DP
              ORDER BY count(DP) DESC
              LIMIT 50;";

      $listCodes = $ds->loadList($sql);

      $list = array();

      foreach ($listCodes as $key => $value) {
        $list[$value["DP"]] = CCodeCIM10::get($value["DP"]);
        $list[$value["DP"]]->occurrences = $value["nb_code"];
      }
    }
  }

  $listByProfile[$profile] = $list;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("type"        , $type);
$smarty->assign("mode"        , $mode);
$smarty->assign("order"       , $order);
$smarty->assign("object_class", $object_class);
$smarty->assign("chir"        , $chir);
$smarty->assign("anesth"      , $anesth);
$smarty->assign("users"       , $users);
$smarty->assign("listByProfile", $listByProfile);
$smarty->assign('curr_user'   , CMediusers::get());
$smarty->display("code_selector_ex");
