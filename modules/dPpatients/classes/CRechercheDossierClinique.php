<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbException;

/**
 * Search for clinical files
 */
class CRechercheDossierClinique implements IShortNameAutoloadable {

  private static $available_types = array(CRechercheDossierClinique::TYPE_PATHOLOGY, CRechercheDossierClinique::TYPE_PROBLEM);

  /** @var string TYPE_PATHOLOGY name of the type 'pathology' of an enum */
  const TYPE_PATHOLOGY = "pathologie";
  /** @var string TYPE_PROBLEM name of the type 'problem' of an enum */
  const TYPE_PROBLEM = "probleme";
  /** @var string TYPE_PATHOLOGY_ALIAS table alias to use in a sql query created by this class */
  const TYPE_PATHOLOGY_ALIAS = "paa";
  /** @var string TYPE_PROBLEM_ALIAS table alias to use in a sql query created by this class */
  const TYPE_PROBLEM_ALIAS = "pra";
  /** @var string TEMP_TABLE_NAME_PATHOLOGY name of the temporary table 'pathology' */
  const TEMP_TABLE_NAME_PATHOLOGY = "pathologieAnd";
  /** @var string TEMP_TABLE_NAME_PROBLEM name of the temporary table 'problem' */
  const TEMP_TABLE_NAME_PROBLEM = "problemeAnd";

  /**
   * Creates the CIM10 sql query for pathologies or problems (reduce)
   *
   * @param array  $list_cim10 - list of the cim codes
   * @param string $type       - 'pathologie' or 'probleme'
   *
   * @return string - the sql query
   */
  static function make_query_cim10($list_cim10, $type) {
    $query = "((";

    $i = 1;
    foreach ($list_cim10 as $_cim10) {
      $query .= "pathologie.code_cim10 = '$_cim10'";
      if ($i < sizeof($list_cim10)) {
        $query .= " OR ";
      }
      $i++;
    }
    $query .= ") AND pathologie.type = '$type')";

    return $query;
  }

  /**
   * Creates the CIM10 sql query for pathologies or problems (in the temporary table)
   *
   * @param string[] $list_cim10 - list of cim codes
   * @param string   $type       - 'pathologie' or 'probleme'
   *
   * @return string - the sql query
   * @throws CMbException
   */
  static function make_query_cim10_temp($list_cim10, $type) {
    if (empty($list_cim10) && !in_array($type, CRechercheDossierClinique::$available_types)) {
      throw new CMbException("No CIM codes or very bad type :(");
    }

    if ($type === CRechercheDossierClinique::TYPE_PATHOLOGY) {
      $alias_type = CRechercheDossierClinique::TYPE_PATHOLOGY_ALIAS;
    }
    if ($type === CRechercheDossierClinique::TYPE_PROBLEM) {
      $alias_type = CRechercheDossierClinique::TYPE_PROBLEM_ALIAS;
    }
    $query = "";

    $i = 1;
    foreach ($list_cim10 as $_cim10) {
      $query .= "find_in_set('$_cim10', $alias_type.codes_cim10)";
      if ($i < sizeof($list_cim10)) {
        $query .= " AND ";
      }
      $i++;
    }

    return $query;
  }

  /**
   * Creates the search text for pathologies and problems
   *
   * @param string $text    - the text to search for
   * @param CRechercheDossierClinique::TYPE_PATHOLOGY|TYPE_PROBLEM $type    - type of issue
   * @param bool   $need_or - is there pathologies and problems
   *
   * @return string - the query
   * @throws CMbException
   */
  static function make_query_text($text, $type, $need_or = false) {
    if (!in_array($type, CRechercheDossierClinique::$available_types)) {
      throw new CMbException("Very bad type :(");
    }

    $query = ($need_or) ? " OR " : "";
    $query .= "(pathologie.pathologie LIKE '%" . $text . "%' AND pathologie.type = '$type')";

    return $query;
  }

  /**
   * Create the temporary table query for pathologies and problems
   *
   * @param CRechercheDossierClinique::TYPE_PATHOLOGY|TYPE_PROBLEM $type  - the name of the table
   * @param string $where - conditions
   *
   * @return string - the query
   * @throws CMbException
   */
  static function make_query_temporary_table($type, $where) {
    if (!in_array($type, CRechercheDossierClinique::$available_types)) {
      throw new CMbException("Very bad type :(");
    }

    $table_name = "";
    if ($type === CRechercheDossierClinique::TYPE_PATHOLOGY) {
      $table_name = CRechercheDossierClinique::TEMP_TABLE_NAME_PATHOLOGY;
    }
    if ($type === CRechercheDossierClinique::TYPE_PROBLEM) {
      $table_name = CRechercheDossierClinique::TEMP_TABLE_NAME_PROBLEM;
    }

    $query = "create temporary table $table_name " .
      "select *, group_concat(code_cim10) as codes_cim10 from pathologie where $where " .
      "group by dossier_medical_id;";

    return $query;
  }

  /**
   * Creates the query to get from the temporary tables
   *
   * @param bool $pathology_active - has the pathology search field been filled in
   * @param bool $problem_active   - has the problem search field been filled in
   *
   * @return string|null - the SQL query
   * @throws CMbException
   */
  static function make_query_get_from_temp_table($pathology_active, $problem_active) {
    if ($pathology_active && $problem_active) {
      $path_alias = CRechercheDossierClinique::TYPE_PATHOLOGY_ALIAS;
      $prob_alias = CRechercheDossierClinique::TYPE_PROBLEM_ALIAS;

      // Both conditions
      return "select " . $path_alias . ".dossier_medical_id " .
        "from " . CRechercheDossierClinique::TEMP_TABLE_NAME_PATHOLOGY . " " . $path_alias . " " .
        "inner join " . CRechercheDossierClinique::TEMP_TABLE_NAME_PROBLEM . " " . $prob_alias . " " .
        "on " . $path_alias . ".dossier_medical_id = " . $prob_alias . ".dossier_medical_id ";
    }
    if ($pathology_active) {
      return "select " . CRechercheDossierClinique::TYPE_PATHOLOGY_ALIAS . ".dossier_medical_id 
              from " . CRechercheDossierClinique::TEMP_TABLE_NAME_PATHOLOGY . " " . CRechercheDossierClinique::TYPE_PATHOLOGY_ALIAS;
    }
    if ($problem_active) {
      return "select " . CRechercheDossierClinique::TYPE_PROBLEM_ALIAS . ".dossier_medical_id 
              from " . CRechercheDossierClinique::TEMP_TABLE_NAME_PROBLEM . " " . CRechercheDossierClinique::TYPE_PROBLEM_ALIAS;
    }

    throw new CMbException("Pathologies or problems must be active");
  }

  /**
   * Creates the query which will dump a temporary table
   *
   * @param CRechercheDossierClinique::TYPE_PATHOLOGY|TYPE_PROBLEM $type - the type of pathology
   *
   * @return string - the sql query
   * @throws CMbException
   */
  static function make_query_dump_temporary_table($type) {
    if ($type === CRechercheDossierClinique::TYPE_PATHOLOGY) {
      return "drop table " . CRechercheDossierClinique::TEMP_TABLE_NAME_PATHOLOGY;
    }
    if ($type === CRechercheDossierClinique::TYPE_PROBLEM) {
      return "drop table " . CRechercheDossierClinique::TEMP_TABLE_NAME_PROBLEM;
    }

    throw new CMbException("Need a type to make a query !");
  }
}