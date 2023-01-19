<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Exception;
use Ox\Cli\MediboardCommand;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class CacheCleanup
 *
 * @package Ox\Cli\Console
 */
class CacheCleanup extends MediboardCommand {
  /** @var OutputInterface */
  protected $output;

  /** @var InputInterface */
  protected $input;

  /** @var QuestionHelper */
  protected $question_helper;

  protected $path;

  /**
   * @see parent::configure()
   */
  protected function configure() {
    $this
      ->setName('ox-cache:cleanup')
      ->setDescription('Clearing Mediboard cache')
      ->setHelp('Performs a cache clean up on several instances')
      ->addOption(
        'path',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Working copy root',
        realpath(__DIR__ . "/../../../")
      )
      ->addArgument(
        'instance_path',
        InputArgument::OPTIONAL,
        'Instance path'
      );
  }

  /**
   * Display header information
   *
   * @return void
   */
  protected function showHeader() {
    $this->out($this->output, '<fg=red;bg=black>Cache cleaner</fg=red;bg=black>');
  }

  /**
   * @param InputInterface  $input  Input Interface
   * @param OutputInterface $output Output Interface
   *
   * @return int
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->input           = $input;
    $this->output          = $output;
    $this->question_helper = $this->getHelper('question');
    $this->path            = $input->getOption('path');

    $instances = [];
    if ($instance_path = $input->getArgument('instance_path')) {
      $instances[] = $instance_path;
    }

    $this->showHeader();

    if (!$instances) {
      $instances = $this->promptInstances();
    }

    $this->clearAll($instances);

    return self::SUCCESS;
  }

  /**
   * @param array $instances Insatnces array
   *
   * @return array
   */
  protected function getIPAddresses($instances) {
    $ip_addresses = array();
    foreach ($instances as $_path) {
      $ip_addresses[$_path] = 'localhost';
      if (preg_match('/(?:[0-9]{1,3}\.){3}[0-9]{1,3}/', $_path, $match)) {
        $ip_addresses[$_path] = $match[0];
      }
    }

    return $ip_addresses;
  }

  /**
   * @param string $host Host
   * @param string $path Path
   *
   * @return bool|string
   */
  protected function createFlagFile($host, $path) {
    $clear_cache_file = "{$path}/tmp/clear_cache.flag";

    if ($host == 'localhost') {
      if (!touch($clear_cache_file)) {
        $this->out($this->output, "<error>Unable to create flag file... Exiting.</error>");

        return false;
      }

      if (!chmod($clear_cache_file, 0755)) {
        $this->out($this->output, "<error>Unable to set permissions to flag file... Exiting.</error>");

        return false;
      }
    }
    else {
      // File creation
      $cmd = escapeshellcmd("ssh $host touch $clear_cache_file");

      $result = array();
      exec($cmd, $result, $state);

      if ($state !== 0) {
        $this->out($this->output, "<error>Error occurred during $cmd...</error>");

        return false;
      }

      $return = implode("\n", $result);

      // Changing permissions
      $cmd = escapeshellcmd("ssh $host chmod 0755 $clear_cache_file");

      $result = array();
      exec($cmd, $result, $state);

      if ($state !== 0) {
        $this->out($this->output, "<error>Error occurred during $cmd...</error>");

        return false;
      }

      return $return . "\n" . implode("\n", $result);
    }

    return true;
  }

  /**
   * @param string $url Url
   *
   * @return bool|string
   */
  protected function clear($url) {
    $http_client = curl_init("http://{$url}/modules/system/public/clear_cache.php");

    curl_setopt($http_client, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($http_client, CURLOPT_TIMEOUT, 10);
    curl_setopt($http_client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($http_client, CURLOPT_FOLLOWLOCATION, true);

    $res  = curl_exec($http_client);
    $info = curl_getinfo($http_client);

    return ($info['http_code'] == "200") ? $res : false;
  }

  /**
   * @param array $instances Instances array
   *
   * @return void
   */
  protected function clearAll($instances) {
    $ip_addresses = $this->getIPAddresses($instances);

    foreach ($instances as $_instance) {
      $url = $ip_addresses[$_instance] . '/' . basename($_instance);

      if ($ip_addresses[$_instance] == 'localhost') {
        $host = 'localhost';
        $path = $_instance;
      }
      else {
        $ssh  = explode(':', $_instance);
        $host = $ssh[0];
        $path = $ssh[1];
      }

      $this->out($this->output, "$_instance ($url) - Clearing cache...");
      if ($this->createFlagFile($host, $path)) {
        $result = $this->clear($url);

        if ($result) {
          $msg = "$_instance ($url) - Cache cleared.";
        }
        else {
          $msg = "$_instance ($url) - Unable to clear cache!";
        }

        $this->out($this->output, $msg);
      }
    }
  }

  /**
   * @return array
   * @throws Exception
   */
  protected function promptInstances() {
    $rsyncupdate_conf = "$this->path/cli/conf/deploy.xml";

    if (!is_readable($rsyncupdate_conf)) {
      throw new Exception("$rsyncupdate_conf is not readable.");
    }

    $dom = new DOMDocument();
    $dom->load($rsyncupdate_conf);
    $xpath = new DOMXPath($dom);

    /** @var DOMNodeList $groups */
    $groups = $xpath->query("//group");

    $all_instances = array();
    /** @var DOMElement $_group */
    foreach ($groups as $_group) {
      $group_name = $_group->getAttribute("name");

      if (!isset($all_instances[$group_name])) {
        $all_instances[$group_name] = array();
      }

      /** @var DOMNodeList $instance_nodes */
      $instance_nodes = $xpath->query("instance", $_group);

      /** @var DOMElement $_instance */
      foreach ($instance_nodes as $_instance) {
        $_path                        = $_instance->getAttribute("path");
        $all_instances[$group_name][] = $_path;
      }
    }

    $instances = array("[ALL]");
    foreach ($all_instances as $_group => $_instances) {
      $instances[] = "[$_group]";

      foreach ($_instances as $_instance) {
        $instances[] = "[$_group] => $_instance";
      }
    }

    $question = new ChoiceQuestion(
      'Select instance (or [group] in order to select all of it)',
      $instances,
      0
    );
    $question->setErrorMessage('Value "%s" is not valid');
    $question->setMultiselect(true);

    $selected = $this->question_helper->ask(
      $this->input,
      $this->output,
      $question
    );

    $this->output->writeln('Selected: ' . implode(', ', $selected));

    foreach ($selected as $_selected) {
      if (preg_match("/\[([A-Za-z]+)\]$/", $_selected, $matches)) {

        // All instances
        if ($matches[1] == "ALL") {
          $all = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($all_instances)), false);

          return $all;
        }

        // All instances from given GROUP
        if (in_array($matches[1], array_keys($all_instances))) {
          foreach ($all_instances[$matches[1]] as $_instance) {
            $selected[] = $_instance;
          }
        }
      }
      else {
        // Single instance
        if (preg_match("/\[[A-Za-z]+\] =\> (.*)/", $_selected, $path)) {
          $selected[] = $path[1];
        }
      }
    }

    // Remove duplicate entries if GROUP and group instances are selected
    $selected = array_unique($selected);

    return $selected;
  }
}
