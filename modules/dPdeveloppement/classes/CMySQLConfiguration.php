<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Exception;
use Ox\Core\CSQLDataSource;

/**
 * MySQL Configuration data gatherer class.
 */
class CMySQLConfiguration extends CDashboardConfiguration {
  static $_variablesWhitelist = array(
    "auto_increment_increment",
    "auto_increment_offset",
    "binlog_format",
    "concurrent_insert",
    "connect_timeout",
    "key_buffer_size",
    "log_bin",
    "log_queries_not_using_indexes",
    "log_slow_queries",
    "long_query_time",
    "max_allowed_packet",
    "max_connections",
    "open_files_limit",
    "query_cache_limit",
    "query_cache_type",
    "relay_log",
    "server_id",
    "skip_networking",
    "slave_net_timeout",
    "slave_skip_errors",
    "slow_query_log",
    "slow_query_log_file",
    "table_open_cache"
  );

  static $_globalStatusWhitelist = array(
    "Byte_sent",
    "Bytes_received",
    "Aborted_clients",
    "Aborted_connects",
    "Connections",
    "default_storage_engine",
    "Max_used_connections",
    "Open_tables",
    "Qcache_free_blocks",
    "Qcache_free_memory",
    "Qcache_hits",
    "Qcache_inserts",
    "Qcache_lowmem_prunes",
    "Qcache_not_cached",
    "Qcache_queries_in_cache",
    "Qcache_total_blocks",
    "Queries",
    "Questions",
    "Slow_queries",
    "Table_locks_immediate",
    "Table_locks_waited",
    "Uptime"
  );

  /**
   * @see parent::init()
   */
  public function init() {
    try {
      $this->extractSystemVariables();
      $this->extractServerStatus();
      $this->getTablesInformation();
    }
    catch (Exception $exception) {
      throw $exception;
    }
  }

  /**
   * Get server variables
   *
   * @return void
   */
  public function extractSystemVariables() {
    $configuration = array();

    $connection = CSQLDataSource::get("std");

    $sql    = "SHOW VARIABLES";
    $result = $connection->loadList($sql);

    foreach ($result as $resultElem) {
      $configuration[$resultElem["Variable_name"]] = $resultElem["Value"];
    }

    foreach (self::$_variablesWhitelist as $variableWhitelist) {
      $mysqlConfigurationVariable = new CMySQLConfigurationVariable($variableWhitelist);
      foreach ($configuration as $key => $confVar) {
        if ($key === $variableWhitelist) {
          $mysqlConfigurationVariable->value            = $confVar;
          $mysqlConfigurationVariable->exists           = true;
          $this->configuration["systemVariables"][$key] = $mysqlConfigurationVariable;
        }
      }
    }
  }

  /**
   * Get global server status and statistics
   *
   * @return void
   */
  public function extractServerStatus() {
    $configuration = array();

    $connection = CSQLDataSource::get("std");

    $sql    = "SHOW GLOBAL STATUS";
    $result = $connection->loadList($sql);

    foreach ($result as $resultElem) {
      $configuration[$resultElem["Variable_name"]] = $resultElem["Value"];
    }

    foreach (self::$_globalStatusWhitelist as $globalVar) {
      $mysqlGlobalVariable = new CMySQLConfigurationVariable($globalVar);
      foreach ($configuration as $key => $confVar) {
        if (strtolower($key) === strtolower($globalVar)) {
          $mysqlGlobalVariable->exists               = true;
          $mysqlGlobalVariable->value                = $confVar;
          $this->configuration["globalStatus"][$key] = $mysqlGlobalVariable;
        }
      }
    }
  }

  public function getDatabasesInformation() {
  }

  /**
   * Get informations about mediboard related tables
   *
   * @return array Returns an array of CMysqlTableInfo
   */
  public function getTablesInformation() {
    $connection = CSQLDataSource::get("std");

      $query         = "SELECT * FROM information_schema.TABLES WHERE ENGINE != 'MyISAM' AND TABLE_SCHEMA != 'information_schema'
       AND TABLE_SCHEMA != 'performance_schema'
       AND TABLE_SCHEMA != 'mysql'
       ORDER BY TABLE_SCHEMA, DATA_LENGTH DESC";

      $preparedQuery = $connection->prepare($query);

      $rawResult         = $connection->loadList($preparedQuery);
      $tablesInformation = array();
      foreach ($rawResult as $row) {
        $mysqlTableInfo = new CMysqlTableInfo();
        $mysqlTableInfo->init($row);

        array_push($tablesInformation, $mysqlTableInfo);
      }

      $this->configuration["tablesInformation"] = $tablesInformation;
  }

  /**
   * Serialize JSON data into CMysqlConfiguration object
   *
   * @param array $jsonData Json data of the mysql configuration
   *
   * @return CMySQLConfiguration
   */
  public static function fromJson($jsonData) {
    $mysqlConfiguration = new CMySQLConfiguration();

    $importedSystemVariables   = $jsonData["configuration"]["systemVariables"];
    $importedServerVariables   = $jsonData["configuration"]["globalStatus"];
    $importedTablesInformation = $jsonData["configuration"]["tablesInformation"];

    foreach ($importedSystemVariables as $key => $importedSystemVariable) {
      $mysqlVariable         = new CMySQLConfigurationVariable($importedSystemVariable["varName"], $importedSystemVariable["value"]);
      $mysqlVariable->exists = $importedSystemVariable["exists"];

      $mysqlConfiguration->configuration["systemVariables"][$key] = $mysqlVariable;
    }

    foreach ($importedServerVariables as $key => $importedServerVariable) {
      $mysqlVariable         = new CMySQLConfigurationVariable($importedServerVariable["varName"], $importedServerVariable["value"]);
      $mysqlVariable->exists = $importedServerVariable["exists"];

      $mysqlConfiguration->configuration["globalStatus"][$key] = $mysqlVariable;
    }

    foreach ($importedTablesInformation as $key => $tableInformations) {
      $mysqlConfiguration->configuration["tablesInformation"][$key] = $tableInformations;
    }

    return $mysqlConfiguration;
  }
}