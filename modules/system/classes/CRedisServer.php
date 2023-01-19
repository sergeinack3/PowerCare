<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
namespace Ox\Mediboard\System;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Redis\CRedisClient;

/**
 * Redis server
 */
class CRedisServer extends CStoredObject {
  const SLAVE_FIX_PROBABILITY = 100;
  const MUTEX_NAME = "elect-redis-master";

  /** @var integer Primary key */
  public $redis_server_id;

  public $host;
  public $port;
  public $instance_role;
  public $is_master;
  public $active;
  public $latest_change;

  public $_connectivity = null;

  public $_information;
  public $_keys_information;
  public $_clients_information;
  public $_slowlog_information;

  public $_conn;

  /** @var self[] */
  static $_servers = null;

  /** @var CRedisClient[] */
  static $_clients = array();

  static $_electing = null;

  /**
   * @inheritdoc
   */
  function getSpec() {
    $spec        = parent::getSpec();
    $spec->table = "redis_server";
    $spec->key   = "redis_server_id";

    return $spec;
  }

  /**
   * @inheritdoc
   */
  function getProps() {
    $props = parent::getProps();

    $props["host"]          = "str notNull";
    $props["port"]          = "num notNull min|0 default|6379";
    $props["instance_role"] = "enum list|prod|qualif";
    $props["is_master"]     = "bool notNull default|1";
    $props["active"]        = "bool notNull default|1";
    $props["latest_change"] = "dateTime notNull";

    return $props;
  }

  /**
   * @inheritdoc
   */
  function store() {
    if (!$this->_id && $this->masterExists()) {
      $this->is_master = 0;
    }

    if (!$this->_id || $this->objectModified()) {
      $this->latest_change = CMbDT::dateTime();
    }

    self::$_servers = null;

    return parent::store();
  }

  /**
   * Tells if a master exists (other than $this)
   *
   * @return bool
   */
  function masterExists() {
    $master = self::getServers("master");

    return $master && $master->_id != $this->_id;
  }

  /**
   * Get all servers matching a type
   *
   * @param string $type The type to count
   *
   * @return int
   */
  function serversCount($type = "all") {
    return count(self::getServers($type));
  }

  /**
   * Makes a connection
   *
   * @param int $timeout Connect timeout
   *
   * @return CRedisClient
   */
  protected function connection($timeout = 5) {
    if ($this->_conn === null) {
      $this->_conn = $this->_getClient($timeout);
    }

    return $this->_conn;
  }

  /**
   * Execute a command to the server
   *
   * @param string $command Redis command
   * @param array  $args    Arguments
   *
   * @return mixed
   */
  function exec($command, $args = array()) {
    return $this->connection()->send($command, $args);
  }

  /**
   * Change a configuration
   *
   * @param string $key   The config's key
   * @param string $value The config's value
   *
   * @return void
   */
  function setConfig($key, $value) {
    $this->exec("CONFIG", array($key, $value));
  }

  /**
   * Check connectivity to a server
   *
   * @return bool|int False when error, a delay in ms to connect
   */
  function checkConnectivity() {
    $t = microtime(true);

    try {
      @$this->exec("INFO");
    }
    catch (Exception $e) {
      return $this->_connectivity = false;
    }

    return $this->_connectivity = 1000 * (microtime(true) - $t);
  }

  /**
   * Get information about the server
   *
   * @return array
   */
  function getInformation() {
    try {
      $info = $this->exec("INFO", array("ALL"));
    }
    catch (Exception $e) {
      // Don't use the second parameter when calling INFO, not supported by Redis 2.4
      $info = $this->exec("INFO");
    }

    // Titles are not present in Redis 2.4
    return $this->_information = $this->connection()->parseMultiLine($info);
  }

  /**
   * Get information about the keys
   *
   * @return array
   */
  function getKeysInformation() {
    $keys = $this->exec("KEYS", array("*"));
    sort($keys);

    $key_info = array();
    foreach ($keys as $_key) {
      $key_info[$_key] = strlen($this->exec("GET", array($_key)));
    }

    return $this->_keys_information = $key_info;
  }

  /**
   * Get clients list information
   *
   * @return array
   */
  function getClientsInformation() {
    $clients = preg_split('/[\r\n]+/', trim($this->exec("CLIENT", array("LIST"))));
    sort($clients);

    $clients_info = array();
    foreach ($clients as $_client) {
      $matches = array();
      preg_match_all('/([\w-]+)=([^ ]+)/', $_client, $matches, PREG_SET_ORDER);

      $_client = array();
      foreach ($matches as $_match) {
        $_client[$_match[1]] = $_match[2];
      }

      $clients_info[] = $_client;
    }

    return $this->_clients_information = $clients_info;
  }

  /**
   * Get slow log information (1000 last entries)
   *
   * @return array
   */
  function getSlowLogInformation() {
    $slowlog = $this->exec("SLOWLOG", array("GET", 1000));

    usort(
      $slowlog,
      function ($a, $b) {
        return $b[2] - $a[2];
      }
    );

    foreach ($slowlog as $_i => $_slowlog) {
      $slowlog[$_i]["datetime"] = CMbDT::strftime(CMbDT::ISO_DATETIME, $_slowlog[1]);
    }

    return $this->_slowlog_information = $slowlog;
  }

  /**
   * Elect the server as master
   *
   * @return bool
   */
  function electAsMaster() {
    if (!$this->_id || self::$_electing == $this->_id) {
      return false;
    }

    self::$_electing = $this->_id;

    /** @var CMbMutex $lock */
    $mutex_class = "CMbMutex";
    try {
      $lock = new $mutex_class(self::MUTEX_NAME);

      // Don't elect if already done by another one
      if (!$lock->lock(10)) {
        return false;
      }
    }
    catch (Exception $e) {
      $mutex_class = "CMbFileMutex";
      $lock = new $mutex_class(self::MUTEX_NAME);

      // Don't elect if already done by another one
      if (!$lock->lock(10)) {
        return false;
      }
    }

    // Get all servers but $this
    $slaves = self::getServers();
    unset($slaves[$this->_id]);

    // Elect master
    try {
      @$this->exec("SLAVEOF", array("NO", "ONE"));
    }
    catch (Exception $e) {
      trigger_error($e->getMessage(), E_USER_WARNING);

      return false;
    }

    // Dominate slaves
    $this->enslaveThem($slaves);

    // Atomic master election in DB
    $ds    = $this->getDS();
    $query = "UPDATE redis_server SET 
      is_master = CASE WHEN redis_server_id  = ?1 THEN '1'
                       WHEN redis_server_id != ?2 THEN '0'
                  END,
      latest_change = ?3
      WHERE instance_role = ?4;";
    $query = $ds->prepare($query, $this->_id, $this->_id, CMbDT::dateTime(), $this->instance_role);
    $ds->exec($query);

    // Clear servers' cache
    self::$_servers = null;

    // Close current clients
    foreach (self::$_clients as $_i => $_client) {
      $_client->close();
      unset(self::$_clients[$_i]);
    }

    // Open a new connection, because the master changed
    $lock = new $mutex_class(static::MUTEX_NAME);
    $lock->release();

    self::$_electing = null;

    return true;
  }

  /**
   * Get a client connection to a Redis server
   *
   * @param int $timeout Connection timeout
   *
   * @return CRedisClient
   */
  protected function _getClient($timeout = 5) {
    $client = new CRedisClient($this->host, $this->port);
    $client->connect($timeout);

    return $client;
  }

    /**
     * Get the real master: the one configured on the Redis serveurs
     *
     * @return $this|CRedisServer|null
     * @throws Exception
     */
  function getRealMaster() {
    // Don't use the second parameter when calling INFO, not supported by Redis 2.4
    $replication = $this->connection()->parseMultiLine($this->exec("INFO"));

    // If it is really a master
    if ($replication["role"] === "master") {
      if (rand(0, self::SLAVE_FIX_PROBABILITY) == 0) {
        $count_slaves = self::serversCount("slave");
        if ($replication["connected_slaves"] < $count_slaves) {
          $slaves = self::getServers("slave");
          $count = @$this->enslaveThem($slaves);

          if ($count < $count_slaves) {
            CApp::log(
                "Log from CRedisServer",
                sprintf("Could not update all slaves' status (%d of %d)", $count, $count_slaves)
            );
          }
        }
      }

      return $this;
    }

    if ($replication["master_link_status"] === "up") {
      $ds = $this->getDS();

      $where = array(
        "active"        => "= '1'",
        "host"          => $ds->prepare("= ?", $replication["master_host"]),
        "port"          => $ds->prepare("= ?", $replication["master_port"]),
        "instance_role" => $ds->prepare("= ?", CAppUI::conf("instance_role")),
      );

      $real_master = new self();
      $real_master->loadObject($where);
      $real_master->electAsMaster();

      return $real_master;
    }

    return null;
  }

  /**
   * Gets the Redis servers' list as an array, from the config
   *
   * @return array
   */
  static function getConfigAdresses() {
    global $dPconfig;

    $conf = trim($dPconfig["shared_memory_params"]) ?: trim($dPconfig["mutex_drivers_params"]["CMbRedisMutex"]);

    $servers = preg_split('/\s*,\s*/', $conf);
    $list    = array();
    foreach ($servers as $_server) {
      $list[] = explode(":", $_server);
    }

    return $list;
  }

  /**
   * @return CRedisClient|null
   * @throws Exception
   */
  static function getClient() {
    $master = self::getServers("master");

    // No Redis server configured
    if (!$master || !$master->_id) {
      $list = self::getConfigAdresses();

      $client = null;
      foreach ($list as $_server) {
        try {
          $client = new CRedisClient($_server[0], $_server[1]);
          $client->connect();
          break;
        }
        catch (Exception $e) {
          $client = null;
        }
      }

      self::$_clients[] = $client;

      return $client;
    }

    if ($master->checkConnectivity()) {
      $real_master = $master->getRealMaster();

      if ($real_master && $real_master->_id) {
        $client = $real_master->_getClient();

        self::$_clients[] = $client;

        return $client;
      }
      else {
        $servers = self::getServers("all");
        shuffle($servers);

        foreach ($servers as $_server) {
          if ($_server->checkConnectivity()) {
            $_server->electAsMaster();

            $client = $_server->_getClient();

            self::$_clients[] = $client;

            return $client;
          }
        }

        // No new master found
        return null;
      }
    }

    $slaves = self::getServers("slave");
    shuffle($slaves);

    foreach ($slaves as $_slave) {
      if ($_slave->checkConnectivity()) {
        $_slave->electAsMaster();

        $client = $_slave->_getClient();

        self::$_clients[] = $client;

        return $client;
      }
    }

    CApp::log("No Redis server reachable");

    return null;
  }

  /**
   * Get Redis servers by type (slave, master, or all)
   *
   * @param string|callable $type The type (slave, master, or all)
   *
   * @return CRedisServer|CRedisServer[]
   */
  static function getServers($type = "all") {
    if (self::$_servers === null) {
      $server = new self();
      if (!$server->isInstalled()) {
        return self::$_servers = array();
      }

      $instance_role = CAppUI::conf("instance_role");

      $where = array(
        "instance_role" => " = '$instance_role'",
        "active"        => " = '1'",
      );

      $order = "host, port";


      self::$_servers = $server->loadList($where, $order);
    }

    switch ($type) {
      default:
        if (is_callable($type)) {
          return array_filter(
            self::$_servers,
            $type
          );
        }

      case "all":
        return self::$_servers;
        break;

      case "slave":
        return array_filter(
          self::$_servers,
          function ($server) {
            return $server->is_master == 0;
          }
        );

        break;

      case "master":
        foreach (self::$_servers as $_server) {
          if ($_server->is_master) {
            return $_server;
          }
        }

        return null;
    }
  }

  /**
   * Makes the list of servers slaves
   *
   * @param CRedisServer[] $slaves Servers to enslave
   *
   * @return int
   */
  function enslaveThem($slaves) {
    $count = 0;
    foreach ($slaves as $_slave) {
      try {
        if ($this->host == $_slave->host && $this->port == $_slave->port) {
          throw new Exception("Tried to put a server as slave of itself, aborting ($this->host:$this->port)");
        }

        @$_slave->exec("SLAVEOF", array($this->host, $this->port));
        $count++;
      }
      catch (Exception $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
      }
    }

    return $count;
  }
}
