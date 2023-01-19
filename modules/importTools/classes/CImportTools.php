<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\ImportTools;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;

/**
 * Import tools class
 */
class CImportTools {
  const E_NO_DESCRIPTION_FILE = 0x01;
  const E_NO_TABLES_DESCRIPTION = 0x02;
  const E_DS_MISCONFIGUATION = 0x04;

  static $classes = array(
    "CPatient",
    "CSejour",
    "CMediusers",
    "CMedecin",
    "CConsultation",
    "CAntecedent",
    "CTraitement",
    "CFile",
    "CCorrespondantPatient",
    "CConsultationCategorie",
    "CFactureCabinet",
    "CReglement",
    "CPlageconsult",
    "CPlageOp",
  );

  static $authorized_operands = array(
    "=", ">", "<", "<=", ">=", "!="
  );

  static $authorized_datatypes = array(
    'str' => array(
      'VARCHAR(255)', 'TEXT'
    ),
    'num' => array(
      'INT(11)', 'FLOAT', 'BOOL'
    )
  );

  /**
   * Get columns information from a table
   *
   * @param CSQLDataSource $ds    Datasource object
   * @param string         $table Table name
   *
   * @return array
   */
  static function getColumnsInfo(CSQLDataSource $ds, $table) {
    $columns = $ds->loadHashAssoc("SHOW COLUMNS FROM `{$table}`");

    if (!$columns) {
      return array();
    }

    $xpath   = null;
    if (!$is_std = self::checkDSN($ds->dsn)) {
      $description = self::getDescription($ds->dsn);
      /** @var DOMXPath $xpath */
      $xpath       = $description->_xpath;
    }

    foreach ($columns as $_name => &$_column) {
      $_column["datatype"]    = $_column["Type"] . " " . ($_column["Null"] == "YES" ? "NULL" : "NOT NULL");
      $_column["is_text"]     = preg_match('/text/', $_column["Type"]);
      $_column["foreign_key"] = null;
      $_column["hide"]        = null;


      if (!$is_std && $xpath) {
        /** @var DOMElement $_column_element */
        $_column_element = $xpath->query("//tables/table[@name='$table']/column[@name='$_name']")->item(0);

        if ($_column_element) {
          $_column["foreign_key"] = $_column_element->getAttribute("foreign_key");
          $_column["hide"]        = $_column_element->getAttribute("hide");
        }
      }
    }

    return $columns;
  }

  /**
   * Get table information
   *
   * @param CSQLDataSource $ds    Datasource object
   * @param string         $table Table name
   *
   * @return array
   */
  static function getTableInfo(CSQLDataSource $ds, $table) {
    $xpath = null;

    if (!$is_std = self::checkDSN($ds->dsn)) {
      $description = self::getDescription($ds->dsn);
      /** @var DOMXPath $xpath */
      $xpath       = $description->_xpath;

      /** @var DOMElement $element */
      $element = $xpath->query("//tables/table[@name='$table']")->item(0);
    }
    else {
      $element = null;
    }

    $query = "SELECT data_length + index_length AS 'size'
            FROM information_schema.TABLES
            WHERE table_schema = ?1
              AND table_name = ?2;";
    $query = $ds->prepare($query, $ds->config["dbname"], $table);

    $size = $ds->loadResult($query);

    $info = array(
      "name"        => $table,
      "title"       => ($element ? utf8_decode($element->getAttribute("title")) : null),
      "display"     => ($element ? ($element->getAttribute("display") != "no") : true),
      "important"   => ($element && ($element->getAttribute("important") == "1")) ? '1' : '0',
      "columns"     => self::getColumnsInfo($ds, $table),
      "class"       => ($element ? $element->getAttribute("class") : null),
      "size"        => $size,
      "primary_key" => null,
    );

    return $info;
  }

  /**
   * Get full database structure
   *
   * @param string $dsn        Datasource name
   * @param bool   $count      Count each table entries
   * @param bool   $basic_info Only gather basics infos
   *
   * @return mixed
   * @throws Exception
   */
  static function getDatabaseStructure($dsn, $count = false, $basic_info = false) {
    if (!$is_std = self::checkDSN($dsn)) {
      $databases = CImportTools::getAllDatabaseInfo();

      if (!isset($databases[$dsn])) {
        throw new Exception("DSN not found : $dsn");
      }

      $db_info = $databases[$dsn];

    }
    else {
      $db_info = array();
    }
    $db_info["tables"] = array();
    $db_info["errors"] = 0;

    $db_credentials = @CAppUI::conf("db $dsn");
    if (!($db_credentials && $db_credentials["dbtype"] && $db_credentials["dbhost"] && $db_credentials["dbname"])) {
      return null;
    }

    $ds = CSQLDataSource::get($dsn, true);

    if (!$is_std) {
      if (!file_exists($db_info["description_file"])) {
        $db_info["errors"] |= self::E_NO_DESCRIPTION_FILE;

        return null;
      }

      // Description file
      $description = new DOMDocument();
      $description->load($db_info["description_file"]);
      $description->_xpath    = new DOMXPath($description);
      $db_info["description"] = $description;

      if ($description->_xpath->query("//tables/table")->length == 0) {
        $db_info["errors"] |= self::E_NO_TABLES_DESCRIPTION;
      }
    }

    if ($ds) {
      // Tables
      $table_names = $ds->loadTables();
      $tables      = array();
      foreach ($table_names as $_table_name) {
        if ($basic_info) {
          $_table_info = null;
        }
        else {
          $_table_info = CImportTools::getTableInfo($ds, $_table_name);

          if ($count) {
            $_table_info["count"] = $ds->loadResult("SELECT COUNT(*) FROM `$_table_name`");
          }
        }

        $tables[$_table_name] = $_table_info;
      }

      $db_info["tables"] = $tables;
    }
    else {
      $db_info["errors"] |= self::E_DS_MISCONFIGUATION;
    }

    return $db_info;
  }

  /**
   * Get a database description DOM document
   *
   * @param string $dsn Datasource name
   *
   * @return DOMDocument|null
   */
  static function getDescription($dsn) {
    static $cache = array();

    if (isset($cache[$dsn])) {
      return $cache[$dsn];
    }

    $databases = self::getAllDatabaseInfo();
    $info      = null;

    foreach ($databases as $_dsn => $_info) {
      if ($_dsn == $dsn) {
        $info = $_info;
        break;
      }
    }

    $description = null;
    if ($info) {
      $description = new DOMDocument();
      $description->load($info["description_file"]);
      $description->_xpath = new DOMXPath($description);
    }

    return $cache[$dsn] = $description;
  }

  /**
   * Load all databases basic information
   *
   * @return array
   */
  static function getAllDatabaseInfo() {
    static $databases = null;

    if ($databases !== null) {
      return $databases;
    }

    $db_meta_files = glob(__DIR__ . "/../../*/db_meta.php");

    $databases = array();
    foreach ($db_meta_files as $_file) {
      $dbs = include_once $_file;

      foreach ($dbs as $_dsn => $_info) {
        $databases[$_dsn] = $_info;
      }
    }

    return $databases;
  }

  /**
   * @param CSQLDataSource $ds      Datasource to use
   * @param string         $table   Name of the table
   * @param array          $select  Type of operand
   * @param array          $columns Columns used for where
   * @param array          $where   Condition used
   *
   * @return CRequest
   */
  static function prepareQuery($ds, $table, $select = null, $columns = null, $where = null) {
    $where = array_filter(
      $where,
      function ($v) {
        return ($v !== null && $v != "");
      }
    );

    $where_req = array();

    $i = 0;
    foreach ($columns as $_col_name => $_value) {
      if (!isset($where[$i])) {
        $i++;
        continue;
      }
      if ($select[$i] == 'like') {
        $where_req[$_col_name] = $ds->prepareLike("$where[$i]");
      }
      elseif (in_array($select[$i], CImportTools::$authorized_operands)) {
        $where_req[$_col_name] = $ds->prepare("$select[$i] ?", $where[$i]);
      }

      $i++;
    }

    $request = new CRequest();
    $request->addSelect("*");
    $request->addTable($table);
    $request->addWhere($where_req);

    return $request;
  }

  static function checkDSN($dsn) {
    if ($dsn === 'std' || $dsn === 'slave') {
      return true;
    }

    return false;
  }
}
