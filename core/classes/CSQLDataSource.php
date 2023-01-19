<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Exceptions\CSQLMaxTimeExecutionReachedException;
use Ox\Core\FileUtil\CCSVFile;
use PDOStatement;

/**
 * Abstract class of SQL Data source engines
 */
abstract class CSQLDataSource
{
    /** @var string[] */
    static $engines = [
        "mysql"      => CPDOMySQLDataSource::class,
        "mysqli"     => CPDOMySQLDataSource::class,
        "oracle"     => COracleDataSource::class,
        "pdo_sqlsrv" => CPDOSQLServerDataSource::class,
        "pdo_mysql"  => CPDOMySQLDataSource::class,
        "pdo_oci"    => CPDOOracleDataSource::class,
    ];

    /** @var CSQLDataSource[] */
    static $dataSources = [];

    /** @var bool */
    static $log = false;

    /**
     * @deprecated
     * @var bool
     */
    static $trace = false;

    /** @var array[] */
    static $log_entries = [];

    /** @var array[] */
    static $report_data = [
        "totals"  => [
            "count"    => 0,
            "duration" => 0,
        ],
        "queries" => [],
    ];

    static $regex_db_version = "/(?P<version>[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})\-(?P<engine>.+)/";

    /** @var string */
    public $dsn = null;

    /** @var resource */
    public $link = null;

    /** @var Chronometer */
    public $chronoInit = null;

    /** @var Chronometer */
    public $chrono = null;

    /** @var Chronometer */
    public $chronoFetch = null;

    /** @var float */
    public $latency = null;

    /** @var float */
    public $connection_time = null;

    /** @var array Columns to be never quoted: hack for some SQLDataSources unable to cast implicitly */
    public $unquotable = [
        "table" => ["column"],
    ];

    /** @var array */
    public $config = [];

    /** @var array Data source meta data (readonly, host status) */
    public $metadata = [
        "readonly" => null,
        //"slave_seconds_behind_master" => null,
        //"slave_last_error"            => null,
        //"slave_running"               => null,
    ];

    /**
     * Init a chronometer
     */
    function __construct()
    {
        $this->chrono      = new Chronometer();
        $this->chronoFetch = new Chronometer();
    }

    /**
     * Get the data source with given name.
     * Create it if necessary
     *
     * @param string $dsn   Data source name
     * @param bool   $quiet Won't trigger errors if true
     *
     * @return CSQLDataSource|null
     */
    static function get($dsn, $quiet = false, $connection_options = [])
    {
        if ($dsn === "std" && CView::$slavestate) {
            $dsn = "slave";
        }

        if (array_key_exists($dsn, self::$dataSources)) {
            return self::$dataSources[$dsn];
        }

        $reporting = null;
        if ($quiet) {
            $reporting = error_reporting(0);
        }

        if (null == $dbtype = CAppUI::conf("db $dsn dbtype")) {
            trigger_error("FATAL ERROR: Undefined type DSN type for '$dsn'.", E_USER_ERROR);
            if ($quiet) {
                error_reporting($reporting);
            }

            return null;
        }

        if (empty(self::$engines[$dbtype])) {
            trigger_error("FATAL ERROR: DSN type '$dbtype' unhandled.", E_USER_ERROR);
            if ($quiet) {
                error_reporting($reporting);
            }

            return null;
        }

        $dsClass = self::$engines[$dbtype];

        /** @var self $dataSource */
        $dataSource = new $dsClass;
        $dataSource->init($dsn, $connection_options, $quiet);
        self::$dataSources[$dsn] = $dataSource->link ? $dataSource : null;

        if ($quiet) {
            error_reporting($reporting);
        }

        return self::$dataSources[$dsn];
    }

    /**
     * Create connection to database
     *
     * @param string $host Host name
     * @param string $name Database name
     * @param string $user Database user name
     * @param string $pass Database user password
     *
     * @return resource|null Database link
     */
    abstract function connect($host, $name, $user, $pass, $connection_options = []);

    /**
     * Make a minimalist query
     *
     * @return bool False if not implemented
     */
    function ping()
    {
        return false;
    }

    /**
     * Close connection
     *
     * @return void
     */
    function close()
    {
    }

    /**
     * Launch the actual query
     *
     * @param string $query SQL query
     *
     * @return resource|bool Query result, false on failure
     */
    abstract function query($query);

    /**
     * Rename a table
     *
     * @param string $old Old name
     * @param string $new New name
     *
     * @return bool job done
     */
    abstract function renameTable($old, $new);

    /**
     * Get the first table like given name
     *
     * @param string $table Table name
     *
     * @return string Table name, empty if not found
     */
    abstract function loadTable($table);

    /**
     * Get all tables with given prefix
     *
     * @param array $table Table names
     *
     * @return string[] Table names
     */
    abstract function loadTables($table = null);

    /**
     * Get the first field like given name in table
     *
     * @param string $table The table
     * @param string $field The field
     *
     * @return string Field name, empty if not found
     */
    abstract function loadField($table, $field);

    /**
     * Get the last error message
     *
     * @return string The message
     */
    abstract function error();

    /**
     * Get the last error number
     *
     * @return integer The number
     */
    abstract function errno();

    /**
     * Get the last autoincremented id
     *
     * @return integer The Id
     */
    abstract function insertId();

    /**
     * Free a query result
     *
     * @param resource $result The result to free
     *
     * @return bool Job done boolean
     */
    abstract function freeResult($result);

    /**
     * Returns number of rows for given result
     *
     * @param resource $result Query result
     *
     * @return int the rows count
     */
    abstract function numRows($result);

    /**
     * Returns number of rows for given result
     *
     * @return int the rows count
     */
    abstract function foundRows();

    /**
     * Number of rows affected by last query
     *
     * @return int the actual number
     */
    abstract function affectedRows();

    /**
     * Get a result row as an enumerative array
     *
     * @param resource $result Query result
     *
     * @return array the result
     */
    abstract function fetchRow($result);

    /**
     * Get a result row as an associative array
     *
     * @param resource $result Query result
     *
     * @return array|false the result or false on error
     */
    abstract function fetchAssoc($result);

    /**
     * Get a result row as both associative and enumerative array
     *
     * @param resource $result Query result
     *
     * @return array the result
     */
    abstract function fetchArray($result);

    /**
     * Get a result row as an object
     *
     * @param resource $result Query result
     * @param string   $class  The class of the returned object
     * @param array    $params Params to be passed to the $class constructor
     *
     * @return object the result
     */
    abstract function fetchObject($result, $class = null, $params = []);

    /**
     * Escape value
     *
     * @param string $value The value to escape
     *
     * @return string the escaped value
     */
    abstract function escape($value);

    /**
     * Get the DB engine version
     *
     * @return string the version number
     */
    abstract function version();

    /**
     * Prepares a LIKE clause with a given value to search
     *
     * @param string $value The LIKE value to prepare
     *
     * @return string The prepared like clause
     */
    abstract function prepareLike($value);

    /**
     * Prepares a LIKE with BINARY clause with a given value to search
     *
     * @param string $value The LIKE value to prepare
     *
     * @return string The prepared like clause
     */
    abstract public function prepareLikeBinary($value): string;

    /**
     * Split the value with space, prepare a LIKE for each token and make an OR between each
     *
     * @param string $value The value to split and search with
     * @param string $field The field to search in
     *
     * @return mixed
     */
    abstract function prepareLikeMulti($value, $field);

    /**
     * Get queries for creation of a base on the server and a user with access to it
     *
     * @param string $user        User name
     * @param string $pass        User password
     * @param string $base        database name
     * @param string $client_host Client host name
     *
     * @return array key-named queries
     */
    abstract function queriesForDSN($user, $pass, $base, $client_host);

    /**
     * Initialize a data source by creating the link to the data base
     *
     * @param string $dsn The data source name to init
     *
     * @return void
     */
    function init($dsn, $connection_options = [], $quiet = false)
    {
        $this->dsn    = $dsn;
        $this->config = CAppUI::conf("db $dsn");

        $hosts = preg_split('/\s*,\s*/', $this->config["dbhost"]);

        $cache = Cache::getCache(Cache::INNER_OUTER);

        // Check readonly status
        $key = "ds_metadata-$dsn";
        if (null === $this->metadata = $cache->get($key)) {
            $links = [];
            $meta  = [];

            foreach ($hosts as $_host) {
                $_link = $this->connect(
                    $_host,
                    $this->config["dbname"],
                    $this->config["dbuser"],
                    $this->config["dbpass"],
                    $connection_options
                );

                $_metadata = [
                    "status" => false,
                ];

                if ($_link) {
                    $this->link = $_link;

                    $_metadata["status"] = true;

                    $result                = $this->loadHash("SHOW VARIABLES LIKE 'read_only';");
                    $_metadata["readonly"] = ($result["Value"] === "ON");

                    $result               = $this->loadHash("SHOW STATUS LIKE 'threads_connected';");
                    $_metadata["threads"] = $result["Value"];

                    // Commented out because of required privileges that must be checked before executing this command...
                    /*$result = $this->loadHash("SHOW SLAVE STATUS;");
                    if ($result) {
                      $_metadata["slave_seconds_behind_master"] = $result["Seconds_Behind_Master"];
                      $_metadata["slave_last_error"] = $result["Last_Error"];
                      $_metadata["slave_running"] = $result["Slave_IO_Running"] === 'Yes' && $result["Slave_SQL_Running"] === 'Yes';
                    }*/
                }

                $meta[$_host]  = $_metadata;
                $links[$_host] = $_link;
            }

            $elected = null;
            foreach ($meta as $_host => $_meta) {
                if (!$elected && $_meta["status"]) {
                    $elected = $_host;
                }

                unset($links[$_host]);
            }

            if (!$elected) {
                $elected = reset($hosts);
            }

            $datasource_meta = [
                "elected"  => $elected,
                "statuses" => $meta,
            ];

            $this->metadata = $datasource_meta;

            $cache->set($key, $datasource_meta, 300);
        }

        $elected = $this->metadata["elected"];

        if ($dsn === "std" && $this->metadata["statuses"][$elected]["readonly"]) {
            CApp::$readonly = true;
        }

        $this->link = $this->connect(
            $elected,
            $this->config["dbname"],
            $this->config["dbuser"],
            $this->config["dbpass"],
            $connection_options
        );

        if (!$this->link && !$quiet) {
            trigger_error("FATAL ERROR: link to '$this->dsn' not found.", E_USER_ERROR);

            return;
        }
    }

    /**
     * Execute a any query
     *
     * @param string $query SQL Query
     *
     * @return resource|bool The result resource on SELECT, true on others, false if failed
     * @throws Exception
     */
    function exec($query)
    {
        // Chrono
        $this->chrono->start();
        $result = $this->query($query);
        $this->chrono->stop();

        // Error handling
        if (!$result) {
            // On slave error, retry exact same request on std
            if ($this->dsn === "slave") {
                $std = self::$dataSources["std"];
                $std->chrono->start();
                $result = $std->query($query);
                $std->chrono->stop();
                if ($result) {
                    CView::disableSlave();

                    return $result;
                }
            }

            $error = $this->error();

            if ($error === 'Query execution was interrupted (max_statement_time exceeded)') {
                throw new CSQLMaxTimeExecutionReachedException($error);
            } else {
                trigger_error("SQL Error: $error for DSN '$this->dsn' on SQL query <em>$query</em>", E_USER_WARNING);
            }

            return false;
        }

        // Query log, durations in micro-seconds
        if (self::$log) {
            self::$log_entries[] = [$query, round($this->chrono->latestStep * 1000000), $this->dsn];
        }

        return $result;
    }

    /**
     * Makes a hash from the query
     *
     * @param string $query The query to hash
     *
     * @return string The hash
     */
    static function hashQuery($query)
    {
        // Turn all word seperator collections into a unique whitespace
        $query = preg_replace('/\s+/', " ", $query);
        // Turn all string parameters into %
        $query = preg_replace('/\'[^\']*\'/', "%", $query);
        // Reduce in % collections into a single %
        $query = preg_replace('/IN ?\([%, ]+\)/', "IN (%)", $query);
        // Turn all numerical parameters into %
        $query = preg_replace('/ \d+/', " %", $query);

        // Hash it
        return md5($query);
    }

    /**
     * Makes a sample from the query
     *
     * @param string $query The query
     *
     * @return string The sample
     */
    static function sampleQuery($query)
    {
        // Limit in collections into 20 items
        return preg_replace('/IN ?\((([^,]+,){10})[^\)]+\)/', "IN ($1 ... )", $query);
    }

    /**
     * Build the SQL report
     *
     * @param null $limit Limit/truncate the number of queries signatures, starting from the most time consuming
     *
     * @return void
     */
    static function buildReport($limit = null)
    {
        $queries =& self::$report_data["queries"];
        $totals  =& self::$report_data["totals"];

        // Compute queries data
        foreach (self::$log_entries as $_entry) {
            [$sample, $duration, $ds] = $_entry;
            $hash = self::hashQuery($sample);
            if (!isset($queries[$hash])) {
                $queries[$hash] = [
                    "sample"       => self::sampleQuery($sample),
                    "ds"           => $ds,
                    "duration"     => 0,
                    "distribution" => [],
                ];
            }

            $query             =& $queries[$hash];
            $query["duration"] += $duration;

            $level = (int)floor(log10($duration) + .5);
            if (!isset($query["distribution"][$level])) {
                $query["distribution"][$level] = 0;
            }
            $query["distribution"][$level]++;

            // Update totals
            $totals["count"]++;;
            $totals["duration"] += $duration;
        }

        // SORT_DESC with SORT_NUMERIC won't work so array is reversed in a second pass
        array_multisort($queries, SORT_ASC, SORT_NUMERIC, CMbArray::pluck($queries, "duration"));
        $queries = array_values(array_reverse($queries));

        if ($limit) {
            $queries = array_slice($queries, 0, $limit);
        }
    }

    /**
     * Displays the SQL report
     *
     * @param array $report_data Report data as input instead of current view report data
     * @param bool  $inline      Echo inline the report
     *
     * @return mixed
     */
    static function displayReport($report_data = null, $inline = true)
    {
        $current_report_data = self::$report_data;

        if ($report_data) {
            self::$report_data = $report_data;
        }

        $queries =& self::$report_data["queries"];
        $totals  =& self::$report_data["totals"];

        // Get counts per query
        foreach ($queries as &$_query) {
            $_query["count"] = array_sum($_query["distribution"]);
        }

        // Report might be truncated versus all query log
        $report_totals = [
            "count"    => array_sum(CMbArray::pluck($queries, "count")),
            "duration" => array_sum(CMbArray::pluck($queries, "duration")),
        ];

        if ($inline) {
            echo '<h2>SQL</h2>';
        }

        if (($report_totals["count"] != $totals["count"]) && $inline) {
            CAppUI::stepMessage(
                UI_MSG_WARNING,
                "Report is truncated to the %d most time consuming query signatures (%d%% of queries count, %d%% of queries duration).",
                count($queries),
                $report_totals["count"] * 100 / $totals["count"],
                $report_totals["duration"] * 100 / $totals["duration"]
            );
        }
        // Unset to prevent second foreach confusion
        unset($_query);

        $report_output = [];

        // Print the report
        foreach ($queries as $_index => $_query) {
            $_dist = $_query["distribution"];
            ksort($_dist);
            $ticks = [
                "  1&micro;  ",
                " 10&micro;  ",
                "100&micro;  ",
                "  1ms ",
                " 10ms ",
                "100ms ",
                "  1s  ",
                " 10s  ",
                "100s  ",
            ];
            $lines = [];
            foreach ($_dist as $_level => $_count) {
                $line = $ticks[$_level];
                $max  = 100;
                while ($_count > $max) {
                    $line   .= str_pad("", $max, "#") . "\n      ";
                    $_count -= $max;
                }
                $line    .= str_pad("", $_count, "#");
                $lines[] = $line;
            }
            $distribution = "<pre>" . implode("\n", $lines) . "</pre>";

            if ($inline) {
                CAppUI::stepMessage(
                    UI_MSG_OK,
                    "Query %d: was called %d times [%d%%] for %01.3fms [%d%%]",
                    $_index + 1,
                    $_query["count"],
                    $_query["count"] * 100 / $totals["count"],
                    $_query["duration"] / 1000,
                    $_query["duration"] * 100 / $totals["duration"]
                );

                echo utf8_decode(CMbString::highlightCode("sql", $_query["sample"], false, "white-space: pre-wrap;"));

                echo $distribution;
            } else {
                $report_output[] = [
                    $_query['ds'],
                    $_query["count"],
                    $_query["duration"] / 1000,
                    $_query["sample"],
                    $distribution,
                ];
            }
        }

        self::$report_data = $current_report_data;

        if (!$inline) {
            return $report_output;
        }
    }

    /**
     * Query an SQL dump
     * Will fail to and exit to the first error
     *
     * @param string $dumpPath  The dump path
     * @param bool   $utfDecode Set to true if the $dumpPath data is encoded in UTF-8
     *
     * @return int Number of queried lines, false if failed
     * @throws Exception
     */
    function queryDump($dumpPath, $utfDecode = false)
    {
        $sqlLines  = file($dumpPath);
        $query     = "";
        $nbQueries = 0;
        foreach ($sqlLines as $lineNumber => $sqlLine) {
            $sqlLine = trim($sqlLine);
            if ($utfDecode) {
                $sqlLine = utf8_decode($sqlLine);
            }

            // Remove empty lignes
            if (!$sqlLine) {
                continue;
            }

            // Remove comment lines
            if (strpos($sqlLine, "--") === 0 || strpos($sqlLine, "#") === 0) {
                continue;
            }

            $query .= $sqlLine;

            // Query at line end
            if (preg_match("/;\s*$/", $sqlLine)) {
                if (!$this->exec($query)) {
                    trigger_error("Error reading dump on line $lineNumber : " . $this->error());

                    return false;
                }
                $nbQueries++;
                $query = "";
            }
        }

        return $nbQueries;
    }

    /**
     * Loads the first field of the first row returned by the query.
     *
     * @param string $query The SQL query
     *
     * @return string|null The value returned in the query or null if the query failed.
     * @throws Exception
     */
    function loadResult($query, ?int $limit_time = null)
    {
        if ($limit_time) {
            $query = $this->limitExecutionTime($query, $limit_time);
        }

        try {
            if (!$result = $this->exec($query)) {
                return false;
            }
        } catch (CSQLMaxTimeExecutionReachedException $e) {
            CAppUI::stepAjax('CSQLDataSource-Error-Max execution time has been reached', UI_MSG_WARNING);

            return false;
        }


        $this->chronoFetch->start();

        $ret = null;
        if ($row = $this->fetchRow($result)) {
            $ret = reset($row);
        }

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $ret;
    }


    /**
     * Loads the first row of a query into an object
     *
     * If an object is passed to this function, the returned row is bound to the existing elements of $object.
     * If $object has a value of null, then all of the returned query fields returned in the object.
     *
     * @param string $query  The SQL query
     * @param object $object The address of variable
     *
     * @return bool
     * @throws Exception
     */
    function loadObject($query, &$object)
    {
        if ($object != null) {
            if (null == $hash = $this->loadHash($query)) {
                return false;
            }

            CMbObject::setProperties($hash, $object);

            return true;
        } else {
            if (!$result = $this->exec($query)) {
                return false;
            }

            $this->chronoFetch->start();

            $object = $this->fetchObject($result);

            $this->chronoFetch->stop();

            $this->freeResult($result);

            if ($object) {
                return true;
            } else {
                $object = null;

                return false;
            }
        }
    }

    /**
     * Execute query and returns first result row as array
     *
     * @param string $query The SQL query
     *
     * @return array|false The hash, false if failed
     * @throws Exception
     */
    function loadHash($query)
    {
        if (!$result = $this->exec($query)) {
            return false;
        }

        $this->chronoFetch->start();

        $hash = $this->fetchAssoc($result);

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $hash;
    }

    /**
     * Returns a array as result of query where column 0 is key and column 1 is value
     *
     * @param string $query The SQL query
     *
     * @return array|false
     * @throws Exception
     */
    function loadHashList($query)
    {
        if (!$result = $this->exec($query)) {
            return false;
        }

        $this->chronoFetch->start();

        $hashlist = [];
        while ($hash = $this->fetchArray($result)) {
            $hashlist[$hash[0]] = $hash[1];
        }

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $hashlist;
    }

    /**
     * Returns a recursive array tree as result of query where successive columns are branches of the tree
     *
     * @param string $query The SQL query
     *
     * @return array|false
     * @throws Exception
     */
    function loadTree($query)
    {
        if (!$result = $this->exec($query)) {
            return false;
        }

        $this->chronoFetch->start();

        $tree = [];
        while ($columns = $this->fetchRow($result)) {
            $branch =& $tree;
            $leaf   = array_pop($columns);
            foreach ($columns as $_column) {
                if (!isset($branch[$_column])) {
                    $branch[$_column] = [];
                }
                $branch =& $branch[$_column];
            }
            $branch = $leaf;
        }

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $tree;
    }

    /**
     * Returns a array as result of query where column 0 is key and all columns are values
     *
     * @param string $query The SQL query
     *
     * @return array|false
     * @throws Exception
     */
    function loadHashAssoc($query)
    {
        if (!$result = $this->exec($query)) {
            return false;
        }

        $this->chronoFetch->start();

        $hashlist = [];
        while ($hash = $this->fetchAssoc($result)) {
            $key            = reset($hash);
            $hashlist[$key] = $hash;
        }

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $hashlist;
    }


    /**
     * Return a list of associative array as the query result
     *
     * @param string $query   The SQL query
     * @param int    $maxrows Maximum number of rows to return
     *
     * @return array|false the query result
     * @throws Exception
     */
    function loadList($query, $maxrows = null, ?int $limit_time = null)
    {
        if ($limit_time) {
            $query = $this->limitExecutionTime($query, $limit_time);
        }

        try {
            if (!$result = $this->exec($query)) {
                return false;
            }
        } catch (CSQLMaxTimeExecutionReachedException $e) {
            CAppUI::stepAjax('CSQLDataSource-Error-Max execution time has been reached', UI_MSG_WARNING);

            return false;
        }


        $this->chronoFetch->start();

        $list = [];
        while ($hash = $this->fetchAssoc($result)) {
            $list[] = $hash;
            if ($maxrows && $maxrows === count($list)) {
                break;
            }
        }

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $list;
    }

    /**
     * Count rows for query
     *
     * @param string $query The SQL query
     *
     * @return bool|int
     * @throws Exception
     */
    function countRows($query)
    {
        if (!$result = $this->exec($query)) {
            return false;
        }

        $this->chronoFetch->start();

        $count = $this->numRows($result);

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $count;
    }

    /**
     * Return a array of the first column of the query result
     *
     * @param string $query   The SQL query
     * @param int    $maxrows Maximum number of rows to return
     *
     * @return array|false the query result
     * @throws Exception
     */
    function loadColumn($query, $maxrows = null)
    {
        if (!$result = $this->exec($query)) {
            return false;
        }

        $this->chronoFetch->start();

        $list = [];
        while ($row = $this->fetchRow($result)) {
            $list[] = $row[0];
            if ($maxrows && $maxrows == count($list)) {
                break;
            }
        }

        $this->chronoFetch->stop();

        $this->freeResult($result);

        return $list;
    }

    /**
     * Insert a row matching object fields
     * null and underscored vars are skipped
     *
     * @param string $table          The table name
     * @param object $object         The object with fields
     * @param array  $vars           The array containing the object's values
     * @param string $keyName        The variable name of the key to set
     * @param bool   $insert_delayed Parameter of INSERT
     *
     * @return bool job done
     * @throws Exception
     */
    function insertObject(
        $table,
        $object,
        $vars,
        $keyName = null,
        $insert_delayed = false/*, $updateDuplicate = false*/
    )
    {
        if ($this->dsn === "slave" || CApp::isReadonly()) {
            return false;
        }

        $fields = [];
        $values = [];

        foreach ($vars as $k => $v) {
            // Skip null, arrays and objects
            if ($v === null || is_array($v) || is_object($v)) {
                continue;
            }

            // Skip underscored
            if ($k[0] === "_") {
                continue;
            }

            // Skip empty vars
            if ($v === "" && $k !== $keyName) {
                continue;
            }

            $v = $this->escape($v);

            // Quote everything
            $this->quote($table, $k, $v);

            // Build array
            $fields[] = $k;
            $values[] = $v;
        }

        $fields_str = implode(",", $fields);
        $values_str = implode(",", $values);
        $delayed    = $insert_delayed ? " DELAYED " : "";
        $query      = "INSERT $delayed INTO $table ($fields_str) VALUES ($values_str)";

        // Update object on duplicate key
        /*if ($updateDuplicate) {
          $update = array();
          foreach ($fields as $_field) {
            if (trim($_field, "`") !== $keyName) {
              $update[] = "$_field = VALUES($_field)";
            }
          }

          if (count($update)) {
            $query .= " ON DUPLICATE KEY UPDATE ".implode(", ", $update);
          }
        }*/

        if (!$result = $this->exec($query)) {
            CAppUI::setMsg($this->error(), UI_MSG_ERROR);

            return false;
        }

        // Valuate id
        $id = $this->insertId();
        if ($keyName && $id) {
            $object->$keyName = $id;
        }

        return true;
    }

    /**
     * Delete a row
     *
     * @param string $table    Table
     * @param string $keyName  Primary key name
     * @param string $keyValue Key value
     *
     * @return bool|resource
     * @throws Exception
     */
    function deleteObject($table, $keyName, $keyValue)
    {
        if (CApp::isReadonly() || $this->dsn === "slave") {
            return false;
        }

        $query = "DELETE FROM $table WHERE $keyName = '$keyValue'";

        return $this->exec($query);
    }

    /**
     * Delete rows
     *
     * @param string   $table     Table
     * @param string   $keyName   Primary key name
     * @param string[] $keyValues Key value
     *
     * @return bool|resource
     * @throws Exception
     */
    function deleteObjects($table, $keyName, $keyValues)
    {
        if (CApp::isReadonly() || $this->dsn === "slave") {
            return false;
        }

        if (!count($keyValues)) {
            return true;
        }

        $query = "DELETE FROM $table WHERE $keyName " . $this->prepareIn($keyValues);

        return $this->exec($query);
    }

    /**
     * Insert multiple rows using data array
     *
     * @param string     $table            Table
     * @param string[][] $data             Collection of fields array
     * @param int        $step             Create a new insert statement after step rows
     * @param bool       $trim             Trim values if true
     * @param bool       $ignore_duplicate Ignore duplicate key errors
     *
     * @return void
     * @throws CMbException
     */
    function insertMulti($table, $data, $step, $trim = true, $ignore_duplicate = false)
    {
        $counter = 0;

        $keys   = array_keys(reset($data));
        $fields = "`" . implode("`, `", $keys) . "`";

        $count_data = count($data);

        $query = null;

        foreach ($data as $_data) {
            if ($counter % $step == 0) {
                $query   = ($ignore_duplicate) ? "INSERT IGNORE INTO `$table` ($fields) VALUES " : "INSERT INTO `$table` ($fields) VALUES ";
                $queries = [];
            }

            $_query = [];
            foreach ($_data as $_value) {
                if ($trim) {
                    $_value = trim($_value);

                    if ($_value === "") {
                        $_query[] = "NULL";
                    } else {
                        $_query[] = "'" . $this->escape($_value) . "'";
                    }
                } else {
                    if ($_value === null) {
                        $_query[] = "NULL";
                    } else {
                        $_query[] = "'" . $this->escape($_value) . "'";
                    }
                }
            }

            $queries[] = "(" . implode(", ", $_query) . ")";

            $counter++;

            if ($counter % $step == 0 || $counter == $count_data) {
                $query .= implode(",", $queries);
                $query .= ";";

                if (!$this->exec($query)) {
                    throw new CMbException($this->error());
                }
            }
        }
    }

    /**
     * Quote columns and values
     *
     * @param string $table Table name
     * @param mixed  $k     in/out column name
     * @param string $v     in/out column value
     *
     * @return void
     */
    function quote($table, &$k, &$v)
    {
        if (!isset($this->unquotable[$table]) || !in_array($k, $this->unquotable[$table])) {
            $v = "'$v'";
        }
        $k = "`$k`";
    }

    /**
     * Update a row matching object fields
     * null and underscored vars are skipped
     *
     * @param string $table               The table name
     * @param array  $vars                The array containing the object's values
     * @param string $keyName             The variable name of the key to set
     * @param bool   $nullifyEmptyStrings Whether to nullify empty values
     *
     * @return bool Job done
     * @throws Exception
     */
    function updateObject($table, $vars, $keyName, $nullifyEmptyStrings = true)
    {
        if ($this->dsn === "slave" || CApp::isReadonly()) {
            return false;
        }

        $tmp   = [];
        $where = null;

        foreach ($vars as $k => $v) {
            // Where clause on key name
            if ($k === $keyName) {
                $where = "`$keyName`='" . $this->escape($v) . "'";
                continue;
            }

            // Skip null, arrays and objects
            if ($v === null || is_array($v) || is_object($v)) {
                continue;
            }

            // Skip underscored
            if ($k[0] === "_") {
                continue;
            }

            $v = $this->escape($v);

            // Quote everything
            $this->quote($table, $k, $v);

            // Nullify empty values or escape
            $v = ($nullifyEmptyStrings && $v === "''") ? "NULL" : $v;

            $tmp[] = "$k=$v";
        }

        // No updates to make;
        if (!count($tmp)) {
            return true;
        }

        $values = implode(",", $tmp);
        $query  = "UPDATE $table SET $values WHERE $where";

        return $this->exec($query) ? true : false;
    }

    /**
     * Escapes up to nine values for SQL queries
     * => prepare("INSERT INTO table_name VALUES (%)", $value);
     * => prepare("INSERT INTO table_name VALUES (%1, %2)", $value1, $value2);
     *
     * @param string $query The query
     *
     * @return string The prepared query
     */
    function prepare($query)
    {
        $values = func_get_args();
        array_shift($values);
        $trans = [];

        foreach ($values as $_i => $_value) {
            $escaped = $this->escape($_value);
            $quoted  = "'$escaped'";
            if ($_i === 0) {
                $trans["%"] = $quoted;
                $trans["?"] = $quoted;
            }
            $key            = $_i + 1;
            $trans["%$key"] = $quoted;
            $trans["?$key"] = $quoted;
        }

        return strtr($query, $trans);
    }

    /**
     * Get database table info
     *
     * @param string $table          Table name
     * @param null   $field          Filter on field name (not sure)
     * @param bool   $reduce_strings Unquote strings and turns them to integer whenever possible
     *
     * @return string[][] Collection of properties
     * @throws Exception
     */
    function getDBstruct($table, $field = null, $reduce_strings = false)
    {
        $list_fields = $this->loadList("SHOW COLUMNS FROM `{$table}`");
        $fields      = [];

        foreach ($list_fields as $curr_field) {
            if (!$field) {
                continue;
            }

            $field_name          = $curr_field['Field'];
            $fields[$field_name] = [];

            $_field =& $fields[$field_name];

            $props = CMbFieldSpec::parseDBSpec($curr_field['Type']);

            $_field['type']     = $props['type'];
            $_field['unsigned'] = $props['unsigned'];
            $_field['zerofill'] = $props['zerofill'];
            $_field['null']     = ($curr_field['Null'] !== 'NO');
            $_field['default']  = $curr_field['Default'];
            $_field['index']    = null;
            $_field['extra']    = $curr_field['Extra'];

            if ($reduce_strings && is_array($props['params'])) {
                foreach ($props['params'] as &$v) {
                    if ($v[0] === "'") {
                        $v = trim($v, "'");
                    } else {
                        $v = (int)$v;
                    }
                }
            }

            $_field['params'] = $props['params'];

            if ($field === $field_name) {
                return $_field;
            }
        }

        return $fields;
    }

    /**
     * Prepares an IN where clause with a given array of values
     * Prepares a standard where clause when alternate value is supplied
     *
     * @param array  $values    The values to include in the IN clause
     * @param string $alternate An alternate value
     *
     * @return string The prepared where clause
     */
    static function prepareIn($values, $alternate = null)
    {
        if ($alternate) {
            return "= '$alternate'";
        }

        // '0' = '1' is multi base compatible
        if (!is_countable($values) || !count($values)) {
            return "IS NULL AND '0' = '1'";
        }

        $quoted = [];
        foreach ($values as $_value) {
            $quoted[$_value] = "'$_value'";
        }

        $str = implode(", ", $quoted);

        return "IN ($str)";
    }

    /**
     * Prepares an BETWEEN where clause with a pair of values
     *
     * @param mixed $start
     * @param mixed $end
     *
     * @return string
     */
    public function prepareBetween($start, $end): string
    {
        return $this->prepare('BETWEEN ?1 AND ?2', $start, $end);
    }

    /**
     * Prepares an LIKE where clause with a given name-like value
     * tread all non non-characters as % wildcards
     *
     * @param string $name The value to include in the LIKE clause
     *
     * @return string The prepared where clause
     */
    function prepareLikeName($name)
    {
        /**
         * Should use the 'u' modifier because we need to match non-ASCII characters with PCRE_UTF8 flag and implicit PCRE_UCP flag,
         * but PHP does not always support UTF8 encoding.
         *
         * Furthermore, we need to have PCRE 16-bit library but PHP uses the PCRE 8-bit version.
         *
         * Finally, we simply manually remove characters with diacritics, because MySQL LIKE operator does the job for us.
         */
        return $this->prepare('LIKE %', preg_replace('/[\W]+/', '_', CMbString::removeDiacritics($name)));
    }

    /**
     * Prepares an NOT IN where clause with a given array of values
     * Prepares a standard where clause when alternate value is supplied
     *
     * @param array  $values    An array of values to include in the IN clause
     * @param string $alternate An alternate value
     *
     * @return string The prepared where clause
     */
    static function prepareNotIn($values, $alternate = null)
    {
        if ($alternate) {
            return "<> '$alternate'";
        }

        if (!count($values)) {
            return "IS NOT NULL AND 1";
        }

        $quoted = [];
        foreach ($values as $value) {
            $quoted[] = "'$value'";
        }

        $str = implode(", ", $quoted);

        return "NOT IN ($str)";
    }

    /**
     * Produce a replace text SQL statement
     *
     * @param string|string[] $search  Search string
     * @param string|string[] $replace Replace string
     * @param string          $subject Subject string
     *
     * @return string
     */
    static function getReplaceQuery($search, $replace, $subject)
    {
        if (!is_array($search)) {
            $search = [$search];
        } else {
            $search = array_values($search); // to have contiguous keys
        }

        if (!is_array($replace)) {
            $replace = [$replace];
        } else {
            $replace = array_values($replace); // to have contiguous keys
        }

        $query = "";

        foreach ($search as $_search) {
            $query .= "REPLACE( \n";
        }

        $query .= $subject; // can be of the form "foo" or foo or `foo`

        $replace_count = count($replace);
        foreach ($search as $i => $_search) {
            $query .= ", '" . addslashes($_search) . "', '" . addslashes($replace[$i % $replace_count]) . "') \n";
        }

        return $query;
    }

    /**
     * Create temporary tables with dates rows
     *
     * @param string $date_min Min date
     * @param string $date_max Max date
     *
     * @return string|null
     * @throws Exception
     * @deprecated
     *
     */
    static function tempTableDates($date_min, $date_max)
    {
        if (!$date_min && !$date_max) {
            return null;
        }

        $date_temp = $date_min;
        $dates     = [];

        while ($date_temp <= $date_max) {
            $dates[]   = "('$date_temp')";
            $date_temp = CMbDT::date("+1 day", $date_temp);
        }

        $ds = CSQLDataSource::get("std");

        $tab_name = substr(uniqid("dates_"), 0, 7);

        $query = "CREATE TEMPORARY TABLE $tab_name (date date not null);";
        $ds->exec($query);

        $query = "INSERT INTO $tab_name VALUES " . implode(",", $dates) . ";";
        $ds->exec($query);

        return $tab_name;
    }

    /**
     * Tell whether a table exists in data source
     * Information is outer cached when true, use self::loadTable() alternative if cache is unwanted
     *
     * @param string $table     Table name
     * @param bool   $use_cache Use the cache or not
     *
     * @return bool
     */
    function hasTable($table, $use_cache = true)
    {
        if ($use_cache) {
            $cache = new Cache('CSQLDataSource.hasTable', [$this->dsn, $table], Cache::INNER_OUTER);
            if ($cache->exists()) {
                return $cache->get();
            }

            // Only cache when true
            $result = (bool)$this->loadTable($table);

            return $result ? $cache->put($result) : $result;
        } else {
            return (bool)$this->loadTable($table);
        }
    }

    /**
     * Tell whether a table exists in data source
     * Information is outer cached when true, use self::loadTable() alternative if cache is unwanted
     *
     * @param string $table Table name
     * @param string $field Field name
     * @param bool   $use_cache
     *
     * @return bool
     */
    function hasField($table, $field, $use_cache = true)
    {
        $cache = new Cache('CSQLDataSource.hasField', [$this->dsn, $table, $field], Cache::INNER_OUTER);
        if ($use_cache && $cache->exists()) {
            return $cache->get();
        }

        // Only cache when true
        $result = self::hasTable($table) && $this->loadField($table, $field);

        return $result ? $cache->put($result) : $result;
    }

    /**
     * Execute a query and return the result into a CSV file
     *
     * @param string   $query      SQL query to exectue
     * @param CCSVFile $csv        Write in an existing CSV
     * @param bool     $write_head Write the header
     *
     * @return CCSVFile
     * @throws Exception
     *
     */
    public function fetchCSVFile(string $query, ?CCSVFile $csv = null, bool $write_head = true): CCSVFile
    {
        $csv = ($csv) ?: new CCSVFile();
        /** @var PDOStatement $res */
        $res = $this->exec($query);

        // Getting the columns names
        $data = $this->fetchAssoc($res);

        // If no data (empty result) check if the header have to be write to the csv and stop.
        if (!$data) {
            if ($write_head) {
                $columns = [];
                for ($i = 0; $i < $res->columnCount(); $i++) {
                    $columns[] = $res->getColumnMeta($i)['name'];
                }

                $csv->setColumnNames($columns);
                $csv->writeLine($columns);
            }

            return $csv;
        }

        // CSV header
        $csv_head = [];
        // First data line
        $first_line = [];

        foreach ($data as $column_name => $value) {
            $csv_head[]   = $column_name;
            $first_line[] = $value;
        }

        $csv->setColumnNames($csv_head);

        if ($write_head) {
            $csv->writeLine($csv_head);
        }

        $csv->writeLine($first_line);

        while ($data = $this->fetchAssoc($res)) {
            $_new_data = [];

            foreach ($csv_head as $_field) {
                $_new_data[] = $data[trim($_field)];
            }

            $csv->writeLine($_new_data);
        }

        return $csv;
    }

    /**
     * Get the DB engine and version
     *
     * @return array
     */
    function getVersionInfos()
    {
        $version = $this->version();
        preg_match(static::$regex_db_version, $version, $matches);

        return [
            "engine"  => isset($matches['engine']) ? $matches['engine'] : null,
            "version" => isset($matches['version']) ? $matches['version'] : null,
        ];
    }

    /**
     * Check if the engine version allow limit on execution time
     *
     * @return bool
     */
    function canLimitExecutionTime()
    {
        return false;
    }

    /**
     * Limit the execution time of a query
     *
     * @param string $query    Query to add limit for
     * @param number $max_time Maximum execution time in seconds
     *
     * @return string
     */
    abstract function limitExecutionTime($query, $max_time);
}
