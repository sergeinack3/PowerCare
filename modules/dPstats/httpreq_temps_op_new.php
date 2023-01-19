<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;

date_default_timezone_set("UTC");

CCanDo::checkAdmin();

// Vide la table finale
$ds = CSQLDataSource::get("std");
$ds->exec("TRUNCATE `temps_op`");

/**
 * Fonction de construction du cache d'info des durées
 * d'hospi et d'interv
 *
 * @param string $tableName   Nom de la table de cache
 * @param string $tableFields Champs de la table de cache
 * @param array  $queryFields Liste des champs du select
 * @param string $querySelect Chaine contenant les éléments SELECT à utiliser
 * @param array  $queryWhere  Chaine contenant les éléments WHERE à utiliser
 *
 * @return void
 */
function buildPartialTables($tableName, $tableFields, $queryFields, $querySelect, $queryWhere) {
  $ds = CSQLDataSource::get("std");

  $joinedFields = implode(", ", $queryFields);

  // Intervale de temps
  $intervalle = CValue::get("intervalle");

  switch ($intervalle) {
    case "month":
      $deb = CMbDT::date("-1 month");
      break;
    case "6month":
      $deb = CMbDT::date("-6 month");
      break;
    case "year":
      $deb = CMbDT::date("-1  year");
      break;
    default:
      $deb = CMbDT::date("-10 year");
  }

  $fin = CMbDT::date();

  // Suppression si existe
  $drop = "DROP TABLE IF EXISTS `$tableName`";
  $ds->exec($drop);

  // Création de la table partielle
  $create = "CREATE TABLE `$tableName` (" .
    "\n`chir_id` int(11) unsigned NOT NULL default '0'," .
    "$tableFields" .
    "\n`ccam` varchar(255) NOT NULL default ''," .
    "\nKEY `chir_id` (`chir_id`)," .
    "\nKEY `ccam` (`ccam`)" .
    "\n) /*! ENGINE=MyISAM */;";

  $ds->exec($create);

  // Remplissage de la table partielle
  $query = "INSERT INTO `$tableName` ($joinedFields, `chir_id`, `ccam`)
    SELECT $querySelect
    operations.chir_id,
    operations.codes_ccam AS ccam
    FROM operations
    LEFT JOIN users
    ON operations.chir_id = users.user_id
    LEFT JOIN plagesop
    ON operations.plageop_id = plagesop.plageop_id
    WHERE operations.annulee = '0'
    $queryWhere
    AND operations.date BETWEEN '$deb' AND '$fin'
    GROUP BY operations.chir_id, ccam
    ORDER BY ccam;";

  $ds->exec($query);
  CAppUI::stepAjax("Nombre de valeurs pour la table '$tableName': " . $ds->affectedRows(), UI_MSG_OK);

  // Insert dans la table principale si vide
  if (!$ds->loadResult("SELECT COUNT(*) FROM temps_op")) {
    $query = "INSERT INTO temps_op ($joinedFields, `chir_id`, `ccam`)
      SELECT $joinedFields, `chir_id`, `ccam`
      FROM $tableName";
    $ds->exec($query);
  }
  // Update pour enrichir en ajoutant des colonnes sinon
  else {
    $query = "UPDATE temps_op, $tableName SET ";

    foreach ($queryFields as $queryField) {
      $query .= "\ntemps_op.$queryField = $tableName.$queryField, ";
    }

    $query .= "temps_op.chir_id = $tableName.chir_id" .
      "\nWHERE temps_op.chir_id = $tableName.chir_id" .
      "\nAND temps_op.ccam = $tableName.ccam";
    $ds->exec($query);
  }
}

// Total des opérations 
$tableName   = "op_total";
$tableFields = "\n`nb_intervention` int(11) unsigned NOT NULL default '0',";
$queryFields = array("nb_intervention");
$querySelect = "\nCOUNT(operations.operation_id) AS total,";
$queryWhere  = "";

buildPartialTables($tableName, $tableFields, $queryFields, $querySelect, $queryWhere);

// Estimations de durées 
$tableName   = "op_estimation";
$tableFields = "\n`estimation` time NOT NULL default '00:00:00',";
$queryFields = array("estimation");
$querySelect = "\nSEC_TO_TIME(AVG(TIME_TO_SEC(operations.temp_operation))) AS estimation,";
$queryWhere  = "";

buildPartialTables($tableName, $tableFields, $queryFields, $querySelect, $queryWhere);

// Occupation de la salle
$tableName   = "op_occup";
$tableFields = "\n`occup_moy` time NOT NULL default '00:00:00',";
$tableFields .= "\n`occup_ecart` time NOT NULL default '00:00:00',";
$queryFields = array("occup_moy", "occup_ecart");
$querySelect = "\nSEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(operations.sortie_salle, operations.entree_salle)))) as occup_moy,";
$querySelect .= "\nSEC_TO_TIME(STD(TIME_TO_SEC(TIMEDIFF(operations.sortie_salle, operations.entree_salle)))) as occup_ecart,";
$queryWhere  = "\nAND operations.entree_salle IS NOT NULL";
$queryWhere  .= "\nAND operations.sortie_salle IS NOT NULL";
$queryWhere  .= "\nAND operations.entree_salle < operations.sortie_salle";

buildPartialTables($tableName, $tableFields, $queryFields, $querySelect, $queryWhere);

// Durée de l'intervention
$tableName   = "op_duree";
$tableFields = "\n`duree_moy` time NOT NULL default '00:00:00',";
$tableFields .= "\n`duree_ecart` time NOT NULL default '00:00:00',";
$queryFields = array("duree_moy", "duree_ecart");
$querySelect = "\nSEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(operations.fin_op, operations.debut_op)))) as duree_moy,";
$querySelect .= "\nSEC_TO_TIME(STD(TIME_TO_SEC(TIMEDIFF(operations.fin_op, operations.debut_op)))) as duree_ecart,";
$queryWhere  = "\nAND operations.debut_op IS NOT NULL";
$queryWhere  .= "\nAND operations.fin_op IS NOT NULL";
$queryWhere  .= "\nAND operations.debut_op < operations.fin_op";

buildPartialTables($tableName, $tableFields, $queryFields, $querySelect, $queryWhere);

// Durée en salle de reveil
$tableName   = "op_reveil";
$tableFields = "\n`reveil_moy` time NOT NULL default '00:00:00',";
$tableFields .= "\n`reveil_ecart` time NOT NULL default '00:00:00',";
$queryFields = array("reveil_moy", "reveil_ecart");
$querySelect = "\nSEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(operations.sortie_reveil_possible, operations.entree_reveil)))) as reveil_moy,";
$querySelect .= "\nSEC_TO_TIME(STD(TIME_TO_SEC(TIMEDIFF(operations.sortie_reveil_possible, operations.entree_reveil)))) as reveil_ecart,";
$queryWhere  = "\nAND operations.entree_reveil IS NOT NULL";
$queryWhere  .= "\nAND operations.sortie_reveil_possible IS NOT NULL";
$queryWhere  .= "\nAND operations.entree_reveil < operations.sortie_reveil_possible";

buildPartialTables($tableName, $tableFields, $queryFields, $querySelect, $queryWhere);

//echo "Liste des temps opératoire mise à jour (".count($listOps)." lignes trouvées)";
