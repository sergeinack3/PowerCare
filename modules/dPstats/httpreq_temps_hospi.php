<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;

date_default_timezone_set("UTC");

$intervalle = CValue::get("intervalle", "none");

// Vide la table contenant les données
$ds = CSQLDataSource::get("std");
$ds->exec("TRUNCATE `temps_hospi`");
$ds->error();

$sql = "SELECT sejour.praticien_id, " .
  "\nCOUNT(sejour.sejour_id) AS total," .
  "\nsejour.type, sejour.sejour_id," .
  "\n(AVG(UNIX_TIMESTAMP(sejour.sortie_reelle)-UNIX_TIMESTAMP(sejour.entree_reelle)))/86400 as duree_hospi," .
  "\n(STD(UNIX_TIMESTAMP(sejour.sortie_reelle)-UNIX_TIMESTAMP(sejour.entree_reelle)))/86400 as ecart_hospi,";

$sql .= "\noperations.codes_ccam AS ccam";
$sql .= "\nFROM operations" .
  "\nLEFT JOIN sejour" .
  "\nON sejour.sejour_id = operations.sejour_id" .
  "\nLEFT JOIN users" .
  "\nON operations.chir_id = users.user_id" .
  "\nWHERE sejour.type != 'exte'" .
  "\nAND sejour.annule = '0'" .
  "\nAND sejour.entree_reelle IS NOT NULL" .
  "\nAND sejour.sortie_reelle IS NOT NULL" .
  "\nAND sejour.sortie_reelle > sejour.entree_reelle";

switch ($intervalle) {
  case "month":
    $sql .= "\nAND sejour.entree_reelle BETWEEN '" . CMbDT::date("-1 month") . "' AND '" . CMbDT::date() . "'";
    break;
  case "6month":
    $sql .= "\nAND sejour.entree_reelle BETWEEN '" . CMbDT::date("-6 month") . "' AND '" . CMbDT::date() . "'";
    break;
  case "year":
    $sql .= "\nAND sejour.entree_reelle BETWEEN '" . CMbDT::date("-1 year") . "' AND '" . CMbDT::date() . "'";
    break;
  default:
    $sql .= "\nAND sejour.entree_reelle BETWEEN '" . CMbDT::date("-10 year") . "' AND '" . CMbDT::date() . "'";
}

$sql .= "\nGROUP BY sejour.type, sejour.praticien_id, operations.codes_ccam";

$listSejours = $ds->loadList($sql);

// Mémorisation des données dans MySQL
foreach ($listSejours as $keylistSejours => $curr_listSejours) {
  // Mémorisation des données dans MySQL
  $sql = "INSERT INTO `temps_hospi` (`temps_hospi_id`, `praticien_id`, `ccam`, `type`, `nb_sejour`, `duree_moy`, `duree_ecart`)
          VALUES (NULL, 
                '" . $curr_listSejours["praticien_id"] . "',
                '" . $curr_listSejours["ccam"] . "',
                '" . $curr_listSejours["type"] . "',
                '" . $curr_listSejours["total"] . "',
                '" . $curr_listSejours["duree_hospi"] . "',
                '" . $curr_listSejours["ecart_hospi"] . "');";
  $ds->exec($sql);
  $ds->error();
}

echo "Liste des temps d'hospitalisation mise à jour (" . count($listSejours) . " lignes trouvées)";
