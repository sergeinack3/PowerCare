<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

date_default_timezone_set("UTC");

CCanDo::checkEdit();

$intervalle = CValue::get("intervalle", "none");

// Liste des Praticiens
$user = new CMediusers();
/** @var CMediusers[] $listPrats */
$listPrats = $user->loadList();

// Initialisation des variables
$preparation = array();
$result      = array();

// Vide la table contenant les données
$ds = CSQLDataSource::get("std");
$ds->exec("TRUNCATE `temps_prepa`");
$ds->error();


foreach ($listPrats as $_prat) {
  //Récupération des opérations par chirurgien

  $sql = "SELECT operations.plageop_id,TIME_TO_SEC(TIME(entree_salle)) AS sec_entree, " .
    "\nTIME_TO_SEC(TIME(sortie_salle)) AS sec_sortie" .
    "\nFROM operations" .
    "\nINNER JOIN plagesop" .
    "\nON operations.plageop_id = plagesop.plageop_id" .
    "\nWHERE operations.chir_id = '$_prat->user_id'" .
    "\nAND annulee = '0'" .
    "\nAND entree_salle IS NOT NULL" .
    "\nAND sortie_salle IS NOT NULL";

  switch ($intervalle) {
    case "month":
      $sql .= "\nAND plagesop.date BETWEEN '" . CMbDT::date("-1 month") . "' AND '" . CMbDT::date() . "'";
      break;
    case "6month":
      $sql .= "\nAND plagesop.date BETWEEN '" . CMbDT::date("-6 month") . "' AND '" . CMbDT::date() . "'";
      break;
    case "year":
      $sql .= "\nAND plagesop.date BETWEEN '" . CMbDT::date("-1 year") . "' AND '" . CMbDT::date() . "'";
      break;
    default:
      $sql .= "\nAND plagesop.date BETWEEN '" . CMbDT::date("-1 month") . "' AND '" . CMbDT::date() . "'";
  }

  $sql .= "\nORDER BY plageop_id, sec_entree ASC";

  $operations = $ds->loadList($sql);

  $old_plagesop                 = 0;
  $old_operation_id             = 0;
  $nb_oper_par_plage            = 0;
  $nb_plage[$_prat->user_id]    = 0;
  $preparation[$_prat->user_id] = array();

  foreach ($operations as $keyOp => $curr_op) {

    if ($old_operation_id and $old_plagesop == $curr_op["plageop_id"]) {
      $testValid = (30 * 60) > ($curr_op["sec_entree"] - $operations[$old_operation_id]["sec_sortie"]);
      $testValid = $testValid && ($operations[$old_operation_id]["sec_sortie"] < $curr_op["sec_entree"]);
      if ($testValid) {
        $preparation[$_prat->user_id][] = $curr_op["sec_entree"] - $operations[$old_operation_id]["sec_sortie"];
        $nb_oper_par_plage++;
        if ($nb_oper_par_plage == 1) {
          $nb_plage[$_prat->user_id] = $nb_plage[$_prat->user_id] + 1;
        }
      }
    }
    else {
      $nb_oper_par_plage = 0;
    }
    $old_operation_id = $keyOp;
    $old_plagesop     = $curr_op["plageop_id"];
  }

  if (count($preparation[$_prat->user_id])) {
    // Mémorisation des données dans MySQL
    $sql = "INSERT INTO `temps_prepa` (`temps_prepa_id`, `chir_id`, `nb_prepa`, `nb_plages`, `duree_moy`, `duree_ecart`)
            VALUES (NULL, 
                '$_prat->user_id',
                '" . count($preparation[$_prat->user_id]) . "',
                '" . $nb_plage[$_prat->user_id] . "',
                '" . CMbDT::strftime("%H:%M:%S", CMbArray::average($preparation[$_prat->user_id])) . "',
                '" . CMbDT::strftime("%H:%M:%S", CMbArray::variance($preparation[$_prat->user_id])) . "');";
    $ds->exec($sql);
    $ds->error();
  }
}
echo("Liste des temps de Préparation mise à jour");
