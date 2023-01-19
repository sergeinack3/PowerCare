<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\Redis\CRedisClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Request command
 */
class Request extends MediboardCommand {
  const MEDIBOARD_LOG = "/var/www/html/tmp/request.log";

  /** @var OutputInterface */
  protected $output;

  /** @var InputInterface */
  protected $input;

  protected $url;
  protected $timeout;
  protected $logfile;
  protected $mutex;
  protected $mutex_master_file;
  protected $redis;
  protected $conf;
  protected $wait_next_minute;
  protected $delay_before;

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->addOption(
        'url',
        'U',
        InputOption::VALUE_OPTIONAL,
        'Root URL',
        'http://localhost/mediboard'
      )
      ->addOption(
        'logfile',
        'o',
        InputOption::VALUE_OPTIONAL,
        'The file for the output',
        Request::MEDIBOARD_LOG
      )
      ->addOption(
        'timeout',
        'T',
        InputOption::VALUE_OPTIONAL,
        'Query timeout'
      )
      ->addOption(
        'mutex',
        'm',
        InputOption::VALUE_OPTIONAL,
        'Mutex server address'
      )
      ->addOption(
        'mutex_master_file',
        'mf',
        InputOption::VALUE_OPTIONAL,
        'Mutex master file, will contain the elected mutex master address, must be writable'
      )
      ->addOption(
        'conf',
        'c',
        InputOption::VALUE_OPTIONAL,
        'Configuration file, can contain all the options available, multiple mutex servers can be set as "mutex[]=xx.xx.xx.xx:yyyy'
      )
      ->addOption(
          'wait_next_minute',
          'w',
          InputOption::VALUE_NONE,
          'Specify if the mutex has to to removed on the next minute'
      )
    ->addOption(
      'delay_before',
      'd',
      InputOption::VALUE_OPTIONAL,
      'Specify how long to wait before job is triggered (in seconds)'
    );
  }

  function getParameters(InputInterface $input) {
    $this->url      = $input->getOption('url');
    $this->logfile  = $input->getOption('logfile');
    $this->timeout  = $input->getOption('timeout');
    $this->mutex    = $input->getOption('mutex');
    $this->conf     = $input->getOption('conf');
    $this->wait_next_minute = $input->getOption("wait_next_minute");
    $this->delay_before = $input->getOption("delay_before");
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->getParameters($input);

    $this->output   = $output;
    $this->input    = $input;

    // Get options from configuration file
    if ($this->conf) {
      if (!is_readable($this->conf)) {
        $this->writeLog("failed to open configuration file '$this->conf'");
        return self::FAILURE;
      }

      $conf = parse_ini_file($this->conf);
      $options = $input->getOptions();

      foreach ($options as $_option => $_value) {
        if (isset($conf[$_option])) {
          $this->{$_option} = $conf[$_option];
        }
      }
    }

    $this->redis = null;

    if ($this->delay_before) {
      $this->writeLog("delayed $this->delay_before s");
      sleep($this->delay_before);
    }

    // If mutex
    $key = null;
    if ($this->mutex) {
      $hash = sha1($this->getUrl(), false);
      $key = "cli-lock-$hash-" . CMbDT::strftime("%Y-%m-%d %H:%M");

      $wait = rand(0, 1000*1000);

      $this->writeLog("waiting for " . $wait / (1000 * 1000) . "s");
      usleep($wait);

      $client = $this->getMutexMaster();

      if (!$client) {
        $this->writeLog("failed to get mutex server");
        return self::FAILURE;
      }

      $this->redis = $client;

      if (!$client->send("SETNX", array($key, 1))) {
        $this->writeLog("mutex already taken");
        return self::FAILURE;
      }
    }

    // Make Mediboard path
    if (!is_dir(dirname($this->logfile))) {
      mkdir(dirname($this->logfile));
    }

    if (!file_exists($this->logfile)) {
      touch($this->logfile);
    }

    $this->call();

    if ($this->redis) {
        // Wait and lock this token/script for the next minute
        if ($this->wait_next_minute) {
            $cur_second = date('s');
            $seconds_to_wait = 59 - $cur_second;

            sleep($seconds_to_wait);
        }

      $this->redis->remove($key);
    }

    return self::SUCCESS;
  }

  /**
   * @param CRedisClient $client
   *
   * @return string|null
   */
  protected function getRole(CRedisClient $client) {
    $info = $client->getStats();

    $infos = preg_split('/[\r\n]+/', $info);
    foreach ($infos as $_info) {
      $_matches = array();
      if (preg_match('/^([^:]+):([^\r\n]+)/', $_info, $_matches)) {
        if ($_matches[1] === "role") {
          return $_matches[2];
        }
      }
    }

    return null;
  }

  /**
   * Get mutex server (must be a master)
   *
   * @return CRedisClient|null
   */
  protected function getMutexMaster() {
    include_once __DIR__ . "/../../core/classes/Redis/CRedisClient.php";
    include_once __DIR__ . "/../../core/classes/Redis/CRedisConnection.php";
    include_once __DIR__ . "/../../core/classes/Chronometer.php";

    $mutex = $this->mutex;
    if ($mutex && !is_array($mutex)) {
      $mutex = array($mutex);
    }

    if (file_exists($this->mutex_master_file)) {
      array_unshift($mutex, file_get_contents($this->mutex_master_file));
      $mutex = array_values($mutex);
    }

    $i = 0;

    do {
      $master = $mutex[$i];

      [$host, $port] = explode(":", $master);

      try {
        $client = new CRedisClient($host, $port);
        $client->connect(1);

        $role = $this->getRole($client);

        if ($role === "master") {
          if ($this->mutex_master_file) {
            $this->writeLog("saving '$master' as master");
            file_put_contents($this->mutex_master_file, $master);
          }

          return $client;
        }
      }
      catch (Exception $e) {
        $this->writeLog("failed to put mutex on '$master'");
      }
    } while ($i++ < count($mutex));

    return null;
  }

    protected function formatText($text)
    {
        return CMbDT::strftime("[%Y-%m-%d %H:%M:%S] ") . $this->getMediboardView() . " " . $text;
    }

  protected function getUrl() {
    // Define it
  }

  /**
   * Root method used to get the called view (token if the view is called with requestToken and the parameters
   * (without user and password) if called with the basic request
   *
   * @return string
   */
  protected function getMediboardView() {
    // Define it
  }

  protected function call() {
    $http_client = curl_init($this->getUrl());

    curl_setopt($http_client, CURLOPT_CONNECTTIMEOUT, 1);

    if ($this->timeout) {
      curl_setopt($http_client, CURLOPT_TIMEOUT, $this->timeout);
    }

    curl_setopt($http_client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($http_client, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($http_client, CURLOPT_SSL_VERIFYHOST, false);

    $this->writeLog("sent");

    curl_exec($http_client);
    $info = curl_getinfo($http_client);

    $this->writeLog(
      "fetched " .
      $info['http_code'] . ' ' .
      intval($info['total_time'] * 1000) . ' ' .
      $info['size_download']
    );

    return ($info['http_code'] == 200);
  }

  protected function writeLog($text) {
    $msg = $this->formatText($text);
    $this->output->writeln($msg);
    file_put_contents($this->logfile, $msg."\n", FILE_APPEND);
  }
}
