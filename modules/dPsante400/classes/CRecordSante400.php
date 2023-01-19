<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbString;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CSQLDataSource;
use PDO;
use PDOException;
use Exception;

class CRecordSante400 implements IShortNameAutoloadable {
  /** @var PDO */
  static $dbh = null;

  /** @var Chronometer */
  static $chrono = null;
  /** @var bool */
  static $verbose = false;
  /** @var bool */
  static $consumeUnsets = true;

  /** @var CPDOMySQLDataSource Fake data source for chrono purposes */
  static $ds = null;
  /** @var string */
  static $last_query = null;
  /** @var array */
  static $last_values = null;

  /** @var array */
  public $data = array();
  /** @var string */
  public $value_prefix = "";

  /**
   * Standard constructor
   */
  function __construct() {
  }

  /**
   * Connect to a AS400 DB2 SQL server via ODBC driver
   *
   * @return void
   * @throws Exception on misconfigured or anavailable server
   *
   */
  static function connect() {
    if (self::$dbh) {
      return;
    }

    $config = CAppUI::conf("sante400");

    if (null == $dsn = $config["dsn"]) {
      throw new Exception("Data Source Name not defined, please configure module", E_USER_ERROR);
    }


    // Fake data source for chrono purposes
    CSQLDataSource::$dataSources[$dsn] = new CPDOMySQLDataSource();
    $ds                                =& CSQLDataSource::$dataSources[$dsn];
    $ds->dsn                           = $dsn;

    self::$chrono =& CSQLDataSource::$dataSources[$dsn]->chrono;
    self::$chrono->start();

    $prefix = $config["prefix"];

    try {
      self::$dbh = new PDO("$prefix:$dsn", $config["user"], $config["pass"]);
      self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        CApp::log("caught failure on first datasource");
      if (null == $dsn = $config["other_dsn"]) {
        throw $e;
      }
      self::$dbh = new PDO("$prefix:$dsn", $config["user"], $config["pass"]);
      self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    self::$chrono->stop("connection");
    self::traceChrono("connection");
  }

  /**
   * Trace a query chrono
   *
   * @param string $trace Trace label
   *
   * @return void
   * @throws \Exception
   */
  static function traceChrono($trace) {
    // Allways log slow queries
    $log_step = floor(self::$chrono->latestStep);
    if ($log_step) {
      $query  = self::$last_query;
      $values = implode(", ", self::$last_values);
        CApp::log("CRecordSante400", "slow '$trace' in '$log_step' seconds");

      if ($trace != "connection") {
          CApp::log("CRecordSante400", "last query was \n $query \n with values [$values]");
      }
    }

    // Trace to output
    if (self::$verbose) {
      $step  = self::$chrono->latestStep * 1000;
      $total = self::$chrono->total * 1000;

      $pace    = floor(2 * log10($step));
      $pace    = max(0, min(6, $pace));
      $message = "query-pace-$pace";
      $type    = floor(($pace + 3) / 2);
      CAppUI::stepMessage($type, $message, $trace, $step, $total);
    }
  }

  /**
   * Trace a query applying syntax coloring
   *
   * @param string $query  Query to execute
   * @param array  $values Values to prepare
   *
   * @return void
   */
  static function traceQuery($query, $values = array()) {
    self::$last_query  = $query;
    self::$last_values = $values;

    // Verbose
    if (!self::$verbose) {
      return;
    }

    // Inject values into query
    foreach ($values as $_value) {
      $_value = str_replace("'", "\\'", $_value);
      $query  = preg_replace("/\?/", "'$_value'", $query, 1);
    }

    echo utf8_decode(CMbString::highlightCode("sql", $query, false, "white-space: pre-wrap;"));
  }

  /**
   * Prepare, execute a query and return multiple records
   *
   * @param string $query  Query to execute
   * @param array  $values Values to prepare
   * @param int    $max    Maximum records returned
   * @param string $class  Records specific class instances
   *
   * @return CRecordSante400[]
   * @throws Exception
   */
  static function loadMultiple($query, $values = array(), $max = 100, $class = CRecordSante400::class) {
    if (!new $class instanceof CRecordSante400) {
      trigger_error("instances of '$class' are not instances of 'CRecordSante400'", E_USER_WARNING);
    }

    $records = array();
    try {
      self::traceQuery($query, $values);
      self::connect();

      // Query execution
      $sth = self::$dbh->prepare($query);
      self::$chrono->start();
      $sth->execute($values);
      self::$chrono->stop("multiple load execute");
      self::traceChrono("multiple load execute");

      // Fetching results
      self::$chrono->start();
      while ($data = $sth->fetch(PDO::FETCH_ASSOC) and $max--) {
        if (CAppUI::conf('dPsante400 fix_encoding')) {
          static::fixEncodingIssues($data);
        }

        $record       = new $class;
        $record->data = $data;
        $records[]    = $record;
        self::$chrono->start();
      }
      self::$chrono->stop("multiple load fetch");
      $count = count($records);
      self::traceChrono("multiple load fetch for '$count' records");
    } catch (PDOException $e) {
      trigger_error("Error querying '$query' : " . $e->getMessage(), E_USER_ERROR);
    }

    return $records;
  }

  /**
   * @param array $data
   *
   * @return void
   */
  private static function fixEncodingIssues(array &$data): void {
    foreach ($data as $_k => &$_v) {
      $_v   = utf8_decode($_v);
      $_pos = strpos($_v, "\x00");

      if ($_pos !== false) {
        $_v = substr($_v, 0, $_pos);
      }

      $_v = trim($_v);
    }
  }

  /**
   * Prepare and execute query
   *
   * @param string $query  Query to execute
   * @param array  $values Values to prepare against
   *
   * @return int the number of affected rows (-1 for SELECTs), false on error;
   * @throws Exception
   */
  function query($query, $values = array()) {
    try {
      self::traceQuery($query, $values);
      self::connect();

      // Query execution and fetching
      $sth = self::$dbh->prepare($query);
      self::$chrono->start();
      $sth->execute($values);

      if (($this->data = $sth->fetch(PDO::FETCH_ASSOC)) && CAppUI::conf('dPsante400 fix_encoding')) {
        static::fixEncodingIssues($this->data);
      }

      self::$chrono->stop("query");
      self::traceChrono("query");
    } catch (PDOException $e) {
      // Fetch throws this exception in case of UPDATE or DELETE query
      if ($e->getCode() == 24000) {
        self::$chrono->stop("query");
        self::traceChrono("query");

        return $sth->rowCount();
      }

      trigger_error("Error querying '$query' : " . $e->getMessage(), E_USER_ERROR);

      return false;
    }
  }

  /**
   * Load a unique record from query
   *
   * @param string $query  Query to execute
   * @param array  $values Values to prepare against
   *
   * @return void the number of affected rows (-1 for SELECTs);
   * @throws Exception if no record fount
   */
  function loadOne($query, $values = array()) {
    $this->query($query, $values);
    if (!$this->data) {
      $values = implode(",", $values);
      throw new Exception("Couldn't find row for query '$query' with values [$values]");
    }
  }

  /**
   * Consume a AS400 DDMMYYYY date and turn it into a SQL ISO date
   *
   * @param string $valueName DDMMYYYY date value name
   *
   * @return string ISO date, null on wrong format
   * @throws Exception
   */
  function consumeDateInverse($valueName) {
    $date = $this->consume($valueName);

    $reg = "/(\d{1,2})(\d{2})(\d{4})/i";

    // Check format anyway
    if (!preg_match($reg, $date)) {
      return null;
    }

    return preg_replace($reg, "$3-$2-$1", $date);
  }

  /**
   * Consume and return any value
   *
   * @param string $valueName Value name
   *
   * @return string Trimmed and slashed value
   * @throws Exception
   */
  function consume($valueName) {
    $valueName = "$this->value_prefix$valueName";

    if (!is_array($this->data)) {
      throw new Exception("The value '$valueName' doesn't exist in this record, which has NO value");
    }

    if (!array_key_exists($valueName, $this->data)) {
      throw new Exception("The value '$valueName' doesn't exist in this record");
    }

    $value = $this->data[$valueName];

    if (self::$consumeUnsets) {
      unset($this->data[$valueName]);
    }

    return trim(addslashes($value));
  }

  /**
   * Lookup any value
   *
   * @param string $valueName Value name
   *
   * @return string Trimmed and slashed value, null if no value
   * @throws Exception
   */
  function lookup($valueName) {
    $valueName = "$this->value_prefix$valueName";

    if (!is_array($this->data)) {
      throw new Exception("Record has NO value, looking up for '$valueName'");
    }

    if (!array_key_exists($valueName, $this->data)) {
      return null;
    }

    $value = $this->data[$valueName];

    return trim(addslashes($value));
  }


  /**
   * Consume and return phone number value
   * Escaping any non-digit character
   *
   * @param string $valueName Value name
   *
   * @return string 10-digit phone number
   * @throws Exception
   */
  function consumeTel($valueName) {
    $value = $this->consume($valueName);
    $value = preg_replace("/(\D)/", "", $value);
    if ($value) {
      $value = str_pad($value, 10, "0", STR_PAD_LEFT);
    }

    return $value;
  }


  /**
   * Consume and assemble two values with a new line separator
   * Escaping any non-digit character
   *
   * @param string $valueName1 Value name 1
   * @param string $valueName2 Value name 2
   *
   * @return string Multi-line value
   * @throws Exception
   */
  function consumeMulti($valueName1, $valueName2) {
    $value1 = $this->consume($valueName1);
    $value2 = $this->consume($valueName2);

    return $value2 ? "$value1\n$value2" : "$value1";
  }

  /**
   * Consume a AS400 YYYYMMDD date and turn it into a SQL ISO date
   *
   * @param string $valueName YYYYMMDD date value name
   *
   * @return string ISO date, null on wrong format
   * @throws Exception
   */
  function consumeDate($valueName) {
    $date = $this->consume($valueName);
    if ($date == "0" || $date == "99999999") {
      return null;
    }

    $reg = "/(\d{4})(\d{2})(\d{2})/i";

    // Check format anyway
    if (!preg_match($reg, $date)) {
      return null;
    }

    return preg_replace($reg, "$1-$2-$3", $date);
  }

  /**
   * Consume a AS400 HHhMM or HHMM time and turn it into a SQL HH:MM:00 time
   *
   * @param string $valueName HHhMM or HHMM time value name
   *
   * @return string HH:MM:00 time
   * @throws Exception
   */
  function consumeTime($valueName) {
    $time = $this->consume($valueName);
    if ($time === "0" || $time == "9999") {
      return null;
    }

    $time = str_pad($time, 4, "0", STR_PAD_LEFT);

    $reg   = "/(\d{2})h?(\d{2})/i";
    $array = array();
    if (!preg_match($reg, $time, $array)) {
      return null;
    }

    // Escape crazy values
    $h = str_pad($array[1] % 24, 2, "0", STR_PAD_LEFT);
    $m = str_pad($array[2] % 60, 2, "0", STR_PAD_LEFT);

    return "$h:$m:00";
  }

  /**
   * Consume a AS400 HH[MM[SS]] flat time and turn it into a SQL ISO time
   *
   * @param string $valueName HH[MM[SS]] flat time value name
   *
   * @return string ISO time
   * @throws Exception
   */
  function consumeTimeFlat($valueName) {
    $time = $this->consume($valueName);
    if ($time === "0") {
      return null;
    }

    $time = str_pad($time, 6, "0", STR_PAD_LEFT);

    $reg = "/(\d{2})(\d{2})(\d{2})/i";

    return preg_replace($reg, "$1:$2:$3", $time);
  }

  /**
   * Consume and assemble AS400 date and flat time into an SQL ISO datetime
   *
   * @param string $dateName YYYYMMDD date time value name
   * @param string $timeName HHhMM or HHMM time value name
   *
   * @return string ISO datetime
   * @throws Exception
   */
  function consumeDateTime($dateName, $timeName) {
    if (null == $date = $this->consumeDate($dateName)) {
      return null;
    }

    if (null == $time = $this->consumeTime($timeName)) {
      $time = "00:00:00";
    }

    return "$date $time";
  }

  /**
   * Consume and assemble AS400 date and flat time into an SQL ISO datetime
   *
   * @param string $dateName YYYYMMDD date time value name
   * @param string $timeName HH[MM[SS]] flat time value name
   *
   * @return string ISO datetime
   * @throws Exception
   */
  function consumeDateTimeFlat($dateName, $timeName) {
    if (null == $date = $this->consumeDate($dateName)) {
      return null;
    }

    if (null == $time = $this->consumeTimeFlat($timeName)) {
      $time = "00:00:00";
    }

    return "$date $time";
  }
}
