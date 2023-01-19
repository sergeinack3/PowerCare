<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Ox\Core\CMbDT;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Mediboard command executer
 */
class MediboardCommand extends Command {
  public $lock_key;
  public $lock_process;
  public $lock_path;
  public $lock_filename;

  /**
   * @see parent::initialize()
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $style = new OutputFormatterStyle('blue', null, array('bold'));
    $output->getFormatter()->setStyle('b', $style);

    $style = new OutputFormatterStyle(null, 'red', array('bold'));
    $output->getFormatter()->setStyle('error', $style);
  }

  /**
   * Output timed text
   *
   * @param OutputInterface $output Output interface
   * @param string          $text   Text to print
   *
   * @return void
   */
    protected function out(OutputInterface $output, $text)
    {
        $output->writeln(CMbDT::strftime("[%Y-%m-%d %H:%M:%S]") . " - $text");
    }

  /**
   * Ensures a directory exists by building all tree sub-directories if possible
   *
   * @param string $dir  Directory path
   * @param int    $mode chmod like value
   *
   * @return boolean job done
   */
  protected function forceDir($dir, $mode = 0755) {
    if (!$dir) {
      trigger_error("Directory is null", E_USER_WARNING);

      return false;
    }

    if (is_dir($dir) || $dir === "/") {
      return true;
    }

    if ($this->forceDir(dirname($dir))) {
      return mkdir($dir, $mode);
    }

    return false;
  }

  protected function initLockFile($path, $key) {
    $this->lock_path    = "$path/tmp/locks";
    $this->lock_process = getmypid();

    $prefix         = preg_replace("/[^\w]+/", "_", $path);
    $this->lock_key = "$prefix-lock-$key";

    $this->lock_filename = "$this->lock_path/$this->lock_key";

    $this->forceDir(dirname($this->lock_filename));
  }

  /**
   * Try to acquire a lock file
   *
   * @param float $lock_lifetime The lock life time in seconds
   *
   * @return bool
   */
  function acquireLockFile($lock_lifetime = 300.0) {
    // No lock, we acquire
    clearstatcache(true, $this->lock_filename);

    if (!file_exists($this->lock_filename)) {
      return touch($this->lock_filename);
    }

    // File exists, we have to check lifetime
    $lock_mtime = filemtime($this->lock_filename);

    // Lock file is not dead
    if ((microtime(true) - $lock_mtime) <= $lock_lifetime) {
      return false;
    }

    // Lock file too old
    $this->releaseLockFile();

    return $this->acquireLockFile();
  }

  /**
   * Release (delete) a lock file
   *
   * @return bool
   */
  function releaseLockFile() {
    clearstatcache(true, $this->lock_filename);

    if (file_exists($this->lock_filename)) {
      return unlink($this->lock_filename);
    }

    return true;
  }

  function getMBConfig($key) {
    global $dPconfig;
    require __DIR__ . '/../../includes/config_all.php';

    // Check if config can be in DB
    $config_db = $this->getConf('config_db', $dPconfig);

    // Fetch config from DB
    if ($config_db) {
      // If config not overridden by config overload, wee need to get it from DB
      $overload_conf = $this->getOverloadConf($key);

      if ($overload_conf === null) {
        $std = $this->getConf('db std', $dPconfig);

        $dbname = $std['dbname'];
        $dbhost = $std['dbhost'];
        $dbuser = $std['dbuser'];
        $dbpass = $std['dbpass'];

        $pdo  = new PDO("mysql:dbname={$dbname};host={$dbhost}", $dbuser, $dbpass);
        $stmt = $pdo->prepare("SELECT `key`, `value` FROM `config_db` WHERE `key` LIKE :pattern;");
        $stmt->execute(array(':pattern' => "{$key}%"));

        $results = $stmt->fetchAll();

        $values = array();
        foreach ($results as $_result) {
          $values[$_result['key']] = $_result['value'];
        }

        return $values;
      }
    }

    return $this->getConf($key, $dPconfig);
  }

  /**
   * Get configuration value from a path and a tree of configuration values
   *
   * @param string $path Config path
   * @param array  $conf Config tree
   *
   * @return null|string
   */
  function getConf($path, $conf) {
    if (!$path) {
      return $conf;
    }

    $items = explode(' ', $path);
    foreach ($items as $part) {
      // dP ugly hack
      if (!array_key_exists($part, $conf) && array_key_exists("dP$part", $conf)) {
        $part = "dP$part";
      }

      if (!@$conf[$part]) {
        return null;
      }

      $conf = $conf[$part];
    }

    return $conf;
  }

  /**
   * Get overloaded configuration value
   *
   * @param string $path Config path
   *
   * @return null|string
   */
  function getOverloadConf($path) {
    $dPconfig = array();

    $file = __DIR__ . "/../../includes/config_overload.php";

    if (is_readable($file)) {
      include $file;
    }

    if (empty($dPconfig)) {
      return null;
    }

    return $this->getConf($path, $dPconfig);
  }
}
