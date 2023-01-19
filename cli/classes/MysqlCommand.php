<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Exception;
use PDO;
use PDOException;

/**
 * Class MysqlCommand
 *
 * Allow to execute shell side mysql commands (mysqlhotcopy...)
 */
class MysqlCommand extends ShellCommand {
  public $mysql_user;
  public $mysql_password;
  public $mysql_host;
  public $dbLockHandler;

  /**
   * MysqlCommand constructor.
   *
   * @param string $hostname       Specify the hostname if the object must perform remote commands
   * @param string $mysql_user     Username of the mysql cli
   * @param string $mysql_password Password of the mysql cli
   * @param string $mysql_host     Server address
   * @param int    $port           Port of the remote hostname
   * @param bool   $use_timeout    Use or not timeout command
   */
  public function __construct($mysql_user = null, $mysql_password = null, $mysql_host = null, $hostname = null, $port = 22, $use_timeout = false) {
    parent::__construct($hostname, $port, $use_timeout);

    $this->mysql_user     = $mysql_user;
    $this->mysql_password = $mysql_password;
    $this->mysql_host     = $mysql_host;
  }

  /**
   * Performs a MysqlHotcopy on the server
   *
   * @param string $database          Database name
   * @param string $hotcopy_directory Directory to perform the hotcopy
   * @param bool   $quiet             Whether it outputs mysqlhotcopy stdout or no
   *
   * @throws Exception
   *
   * @return array
   */
  public function mysqlhotcopy($database, $hotcopy_directory, $quiet = true) {
    //Checking if the database exists
    if (!$this->databaseExists($database)) {
      throw new Exception('Cannot perform mysqlhotcopy on the server: ' . $database . ' database does not exists');
    }

    $quiet_argument = '';
    $user_argument = '-u ' . escapeshellarg($this->mysql_user);
    $password_argument = '-p ' . escapeshellarg($this->mysql_password);

    if ($quiet) {
      $quiet_argument = ' --quiet';
    }

    $command = "mysqlhotcopy"
                . $quiet_argument . ' '
                . $user_argument . ' '
                . $password_argument . ' '
                . $database . ' '
                . $hotcopy_directory;

    return $this->runCommand($command);
  }

  /**
   * @return string
   */
  private function dbConnect($dbname='information_schema') {
    $dsn = 'mysql:host=' . $this->mysql_host . ';dbname=' . $dbname;

    try {
      $dbHandler = new PDO($dsn, $this->mysql_user, $this->mysql_password);
    }
    catch (Exception $e) {
      echo $e->getMessage() . "\n";

      return false;
    }

    return $dbHandler;
  }

  /**
   * Returns the master status of a sql server
   *
   * @return bool
   * @throws Exception
   */
  public function showMasterStatus() {

    $dbHandler = $this->dbConnect('information_schema');

    $sql_query = "SHOW MASTER STATUS";

    $statement = $dbHandler->prepare($sql_query);

    if (!$statement->execute()) {
      throw new Exception($statement->errorInfo()[2]);
    }

    $status = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (count($status) === 0) {
      return false;
    }

    return $status[0];
  }

  /**
   * Flush all tables of a given database
   * @param $db_name
   */
  public function flushTables($db_name) {
    $dbHandler = $this->dbConnect($db_name);

    $sql_query = "FLUSH TABLES :tables";

    $tables = $this->getTablesInfo($db_name);

    if (!is_array($tables) || count($tables) === 0) {
      $this->last_error = "<error>Cannot get a list of tables for database $db_name</error>";

      return false;
    }

    $tablesStrList = $this->getTablesStrList($db_name, $tables);

    $statement = $dbHandler->prepare($sql_query);

    try {
      $ok = $statement->execute(array(':tables' => $tablesStrList));
    }
    catch (PDOException $e) {

      $errorInfo = $statement->errorInfo();
      echo $errorInfo[2] . "\n";

      return false;
    }

    return true;
  }

  /**
   * Checks if a database exists
   *
   * @param string $database_name Name of the database
   *
   * @return bool
   */
  protected function databaseExists($database_name) {
    $dbHandler = $this->dbConnect('information_schema');

    $sql_query = "SELECT COUNT(TABLE_SCHEMA) AS nb_db
                  FROM TABLES
                  WHERE TABLE_SCHEMA=:db_name";

    $statement = $dbHandler->prepare($sql_query);

    if (!$statement->execute(array(':db_name' => $database_name))) {
      $errorInfo = $statement->errorInfo();
      echo $errorInfo[2] . "\n";

      return false;
    }

    $nb = $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($nb[0]['nb_db'] > 0) {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Gets a list of tables with their size (Data + Index Size)
   *
   * @param $db_name Db to perform the listing
   *
   * @return array List of tables with their size
   */
  public function getTablesInfo($db_name) {
    $sql_query = "SELECT 
                    TABLE_SCHEMA,
                    TABLE_NAME,
                    ENGINE,
                    TABLE_ROWS,
                    ROUND((DATA_LENGTH + INDEX_LENGTH), 2) AS 'TABLE_SIZE',
                    DATA_LENGTH,
                    INDEX_LENGTH,
                    CREATE_TIME,
                    UPDATE_TIME
                  FROM TABLES
                  WHERE TABLE_SCHEMA=:db_name";

    $dbHandler = $this->dbConnect('information_schema');

    try {
      $statement = $dbHandler->prepare($sql_query);
    }
    catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }

    $ok = $statement->execute(array(':db_name' => $db_name));

    if (!$ok) {
      $pdo_error_message = $statement->errorInfo();
      $this->out(
        $this->output,
        '<error>' . $pdo_error_message[2] . '</error>'
      );

      return array();
    }

    return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Locks all tables of a given databases
   * @param $db_name
   *
   * @return bool
   */
  public function lockDatabase($db_name) {
    $tables = $this->getTablesInfo($db_name);

    if (!is_array($tables) || count($tables) === 0) {
      $this->last_error = "<error>Cannot get a list of tables for database $db_name</error>";

      return false;
    }

    if (!$this->dbLockHandler) {
      $this->last_error = "<error>Cannot connect to mysql database</error>";
      return false;
    }

    $tables_str_buffer = $this->getTablesStrList($db_name, $tables);

    $sql = "LOCK TABLES " . $tables_str_buffer . " WRITE";

    $this->dbLockHandler->beginTransaction();
    $statement = $this->dbLockHandler->prepare($sql);

    try {
      $ok = $statement->execute(array(':tablesList' => $tables_str_buffer));
    } catch (PDOException $e) {
      $this->last_error = $e->getMessage();
      return false;
    }
    $this->dbLockHandler->commit();

    return true;
  }

  /**
   * Unlocks all previously locked tables
   */
  public function unlockTables() {
    $dbHandler = $this->dbConnect();

    $sql = "UNLOCK TABLES";

    $statement = $dbHandler->prepare($sql);

    try {
      $ok = $statement->execute();
    } catch (PDOException $e) {
      $this->last_error = $e->getMessage();
      return false;
    }

    return true;
  }

  /**
   * @param $db_name string
   * @param $tables array
   * @param $separator string
   *
   * Get a string list of tables separated by a
   *
   * @return string
   */
  private function getTablesStrList($db_name, $tables, $separator = ', ') {
    // Creating list of tables
    $i = 0;
    $tables_str_buffer = '';
    foreach ($tables as $table) {
      if ($table['TABLE_SCHEMA'] === $db_name) {
        $table_name = $table['TABLE_NAME'];

        $tables_str_buffer .= $db_name . "." . $table_name;

        if ($i < count($tables) - 1) {
          $tables_str_buffer .= $separator;
        }
      }
      $i++;
    }

    return $tables_str_buffer;
  }
}

