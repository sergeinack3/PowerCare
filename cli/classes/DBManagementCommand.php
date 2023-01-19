<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use DOMDocument;
use DOMXPath;
use Exception;
use PDO;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


/**
 * Class DBManagementCommand
 */
abstract class DBManagementCommand extends MediboardCommand {
  /** @var OutputInterface */
  protected $output;
  /** @var  InputInterface */
  protected $input;
  /** @var QuestionHelper */
  protected $question_helper;
  /** @var  MysqlCommand */
  protected $shell;
  /** @var  PDO */
  protected $dbHandler;
  /** @var  string */
  protected $server_host = 'localhost';
  /** @var  string */
  protected $server_ssh_user = 'root';
  /** @var  string */
  protected $mysql_user;
  /** @var  string */
  protected $mysql_password;
  /** @var  string */
  protected $backup_path;
  /** @var  string */
  protected $temporary_path;
  /** @var  string */
  protected $mysql_datadir;
  /** @var string */
  protected $config_file;
  /** @var  bool */
  protected $use_encryption = false;
  /** @var  string */
  protected $encryption_password;
  /** @var  string */
  protected $encryption_algorithm = 'aes-256-cbc';

  /**
   * DBManagementCommand constructor.
   *
   * @param string $name name
   */
  public function __construct($name = null) {
    parent::__construct($name);
    $this->config_file = __DIR__  .'/../conf/mysqlbackup.xml';
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Surcharger ça
    throw new Exception(__METHOD__ . " must be redefined");
  }

  /**
   * Opens and returns a DomXpath object representing $this->config_file xml file
   *
   * @throws NotificationException
   *
   * @return DOMXPath
   */
  protected function getXMLConfigurationFile() {
    if (!is_readable($this->config_file)) {
      throw new NotificationException($this->config_file . ' cannot be read');
    }

    $dom = new DOMDocument();
    $dom->load($this->config_file);
    $xpath = new DOMXPath($dom);

    return $xpath;
  }

  /**
   * Loads script configuration from the xml file
   *
   * @throws NotificationException
   *
   * @return void
   */
  abstract protected function loadConfig();


  /**
   * Display a prompt to ask for a path
   *
   * @param string $prompt  Message to be prompt for user action
   * @param string $default Default value
   *
   * @return string
   */
  protected function promptPathAskingWidget($prompt, $default = '') {
    $value         = false;
    $default_label = ($default ? '[default: ' . $default . ']' : '');

    $question = new Question($prompt . ' ' . $default_label . ': ', $default);
    $question->setValidator(
      function ($answer) {
        $match = preg_match('#^(\/.[^\h]+)$#', $answer);
        if (!$match) {
          $this->out(
            $this->output,
            '<comment>The path \'' . $answer . '\' is not a valid path</comment>'
          );
          return false;
        }
        return $answer;
      }
    );

    while (false === $value) {
      $value = $this->question_helper->ask($this->input, $this->output, $question);
    }

    return $value;
  }

  /**
   * Convenience method used to set the hostname to the shell command as well as set mysql settings
   *
   * @return void
   */
  protected function setShellCommandHostname() {
    if ($this->server_host !== 'localhost' && $this->server_host !== '127.0.0.1') {
      $this->shell->setHostname('root@' . $this->server_host);
      $this->shell->useSSH(true);
    }

    $this->shell->mysql_host     = $this->server_host;
    $this->shell->mysql_user     = $this->mysql_user;
    $this->shell->mysql_password = $this->mysql_password;
  }

  /**
   * Checks if the user is root
   */
  protected function isRoot() {
    if (posix_geteuid() != 0) {
      return false;
    }

    return true;
  }
}