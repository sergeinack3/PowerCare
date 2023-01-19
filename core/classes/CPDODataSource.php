<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Class CPDODataSource
 */
abstract class CPDODataSource extends CSQLDataSource {
  protected $driver_name;
  protected $affected_rows;

  /** @var PDO */
  public $link;

  /**
   * @inheritdoc
   */
  function connect($host, $name, $user, $pass, $connection_options = []) {
    if (!class_exists(PDO::class)) {
      trigger_error("FATAL ERROR: PDO support not available. Please check your configuration.", E_USER_ERROR);

      return null;
    }

    $host_port = explode(":", $host, 2);

    $dsn = "$this->driver_name:dbname=$name;host=$host_port[0]";

    if (count($host_port) === 2) {
      $dsn .= ";port=$host_port[1]";
    }

    $options = CMbArray::mergeKeys($connection_options, [PDO::ATTR_TIMEOUT => 1,]);

    // Measure connection time
    $t = microtime(true);

    try {
      $link = new PDO($dsn, $user, $pass, $options);
    }
    catch (PDOException $e) {
      trigger_error($e->getMessage() . " (DSN = $dsn)", E_USER_ERROR);

      return null;
    }

    $this->connection_time = (microtime(true) - $t) * 1000;

    $this->link = $link;

    // Disable query cache
    if (isset($this->config["nocache"]) && $this->config["nocache"]) {
      $this->disableCache();
    }

    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // Since PHP 8.1 integer values are returned as int
    // @link https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.pdo.mysql
    $link->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

    return $link;
  }

  /**
   * Make a minimalist query
   *
   * @return bool
   */
  function ping() {
    $this->link->query("SELECT 1");

    return true;
  }

  /**
   * Close connection
   *
   * @return void
   */
  function close() {
    unset($this->link);
  }

  /**
   * Disable query cache
   *
   * @return void
   */
  function disableCache() {

  }

  /**
   * @inheritdoc
   */
  function error() {
    $errorInfo = $this->link->errorInfo();

    return $errorInfo[2];
  }

  /**
   * @inheritdoc
   */
  function errno() {
    return $this->link->errorCode();
  }

  /**
   * @inheritdoc
   */
  function insertId() {
    return $this->link->lastInsertId();
  }

  /**
   * @inheritdoc
   */
  function query($query) {
    $stmt = $this->link->query($query);

    if ($stmt !== false) {
      $this->affected_rows = $stmt->rowCount();
    }

    return $stmt;
  }

  /**
   * @param PDOStatement $result Statement
   *
   * @return void
   */
  function freeResult($result) {
    $result->closeCursor();
  }

  /**
   * @inheritdoc
   *
   * @param PDOStatement $result
   */
  function numRows($result) {
    return $result->rowCount();
  }

  /**
   * @inheritdoc
   */
  function affectedRows() {
    return $this->affected_rows;
  }

  /**
   * @inheritdoc
   */
  function foundRows() {
    // No such implementation
  }

  /**
   * @param PDOStatement $result Statement
   *
   * @return array
   */
  function fetchRow($result) {
    return $result->fetch(PDO::FETCH_NUM);
  }

    /**
     * @param PDOStatement $result
     *
     * @return array
     */
  function fetchAssoc($result) {
    return $result->fetch(PDO::FETCH_ASSOC);
  }

    /**
     * @param PDOStatement $result
     *
     * @return array
     */
  function fetchArray($result) {
    return $result->fetch(PDO::FETCH_BOTH);
  }

    /**
     * @param PDOStatement $result
     * @param $class_name
     * @param $params
     *
     * @return object
     */
  function fetchObject($result, $class_name = null, $params = array()) {
    if (empty($class_name)) {
      return $result->fetchObject();
    }

    if (empty($params)) {
      return $result->fetchObject($class_name);
    }

    return $result->fetchObject($class_name, $params);
  }

  function escape($value) {
    return substr($this->link->quote($value ?? ""), 1, -1); // remove the quotes around
    /*
    return strtr($value, array(
      "'" => "''",
      '"' => '\"',
    ));*/
  }

  function prepareLike($value) {
    $value = preg_replace('/\\\\/', '\\\\\\', $value);

    return $this->prepare("LIKE %", $value);
  }

 public function prepareLikeBinary($value): string
 {
     $value = preg_replace('`\\\\`', '\\\\\\', $value);
     return $this->prepare("LIKE BINARY %", $value);
 }

  /**
   * @inheritdoc
   */
  function prepareLikeMulti($value, $field, $table = null) {
    $tokens = explode(' ', $value);
    $parts  = array();
    foreach ($tokens as $_token) {
      $parts[] = ($table ? "`$table`." : "") . "`$field` " . $this->prepareLike("%$_token%");
    }

    return implode(' OR ', $parts);
  }

  function version() {
    return null;
  }

  function renameTable($old, $new) {
    $query = "ALTER TABLE `$old` RENAME TO `$new`";

    return $this->exec($query);
  }

  function loadTable($table) {
    $query = $this->prepare("SHOW TABLES LIKE %", $table);

    return $this->loadResult($query);
  }

  function loadTables($table = "") {
    $query = $this->prepare("SHOW TABLES LIKE %", "$table%");

    return $this->loadColumn($query);
  }

  function loadField($table, $field) {
    $query = $this->prepare("SHOW COLUMNS FROM `$table` LIKE %", $field);

    return $this->loadResult($query);
  }

  function queriesForDSN($user, $pass, $base, $client_host) {
    $queries = array();

    // Create database
    $queries["create-db"] = "CREATE DATABASE `$base`;";

    // Create user with global permissions
    $queries["global-privileges"] = $this->prepare(
      "GRANT USAGE
        ON * . * 
        TO ?1@?2
        IDENTIFIED BY ?3;", $user, $client_host, $pass
    );

    // Grant user with database permissions
    $queries["base-privileges"] = $this->prepare(
      "GRANT ALL PRIVILEGES
        ON `$base` . *
        TO ?1@?2;", $user, $client_host
    );

    return $queries;
  }

  /**
   * Limit the execution time of a query
   *
   * @param string $query    Query to add limit for
   * @param number $max_time Maximum execution time in seconds
   *
   * @return string
   */
  function limitExecutionTime($query, $max_time) {
    return $query;
  }
}
