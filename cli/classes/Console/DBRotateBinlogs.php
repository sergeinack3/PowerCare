<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use DateTime;
use Exception;
use Ox\Cli\DBManagementCommand;
use Ox\Cli\MysqlCommand;
use Ox\Cli\NotificationException;
use Ox\Cli\ShellCommand;
use PDO;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class DBRotateBinlogs
 */
class DBRotateBinlogs extends DBManagementCommand {
  /** @var OutputInterface */
  protected $output;
  /** @var InputInterface */
  protected $input;
  /** @var string */
  protected $binlogs_directory;
  /** @var string */
  protected $binlogs_index_filename;
  /** @var int */
  protected $live_binlogs_retention_policy;
  /** @var int */
  protected $archive_binlogs_retention_policy;

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('db:binlogsrotate')
      ->setDescription('Binlogs rotation')
      ->setHelp('Performs a rotation and archiving of the binlogs')
      ->addArgument(
        'mysql_user',
        InputArgument::REQUIRED,
        'Mysql username'
      )
      ->addArgument(
        'mysql_password',
        InputArgument::REQUIRED,
        "Mysql user's password"
      )
      ->addOption(
        'encryption_password',
        'c',
        InputOption::VALUE_OPTIONAL,
        'Password for the encryption'
      )
      ->addOption(
        'encryption_algorithm',
        'a',
        InputOption::VALUE_OPTIONAL,
        'Encryption algorithm'
      );
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int
   * @throws NotificationException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->input  = $input;
    $this->output = $output;
    $this->shell  = new MysqlCommand();
    //$this->shell->verbose = true;

    $this->loadConfig();
    $this->processArguments();

    if (!$this->isRoot()) {
      $error_msg = '<error>'
        . "ERROR\n"
        . "This script must be executed as root since it requires to manipulate mysql owned content!"
        . '</error>';

      throw new NotificationException($error_msg);
    }

    $this->setShellCommandHostname();

    $dsn = 'mysql:dbname=mysql;host=' . $this->server_host;

    try {
      $this->dbHandler = new PDO($dsn, $this->mysql_user, $this->mysql_password);
    }
    catch (Exception $e) {
      throw new NotificationException(
        "<error>Cannot connect to the database\n" . $e->getMessage() . "</error>",
        $e->getCode(),
        $e
      );
    }

    try {
      $this->performBinlogsRotation();
    }
    catch (Exception $e) {
      throw new NotificationException($e->getMessage());
    }

    $this->out(
      $this->output,
      '<info>Binlogs rotation done</info>'
    );

    return self::SUCCESS;
  }

  /**
   * @inheritdoc
   */
  protected function loadConfig() {
    $xpath = $this->getXMLConfigurationFile();

    $server_address                   = $xpath->query("//server_address")->item(0);
    $server_ssh_user                  = $xpath->query("//ssh_user")->item(0);
    $backup_path                      = $xpath->query("//backup_path")->item(0);
    $temporary_path                   = $xpath->query("//temporary_path")->item(0);
    $binlogs_directory                = $xpath->query('//binlogs_rotation/binlogs_directory')->item(0);
    $binlogs_index_filename           = $xpath->query('//binlogs_rotation/binlogs_index_filename')->item(0);
    $live_binlogs_retention_policy    = $xpath->query('//binlogs_rotation/live_binlogs_retention_policy')->item(0);
    $archive_binlogs_retention_policy = $xpath->query('//binlogs_rotation/archive_binlogs_retention_policy')->item(0);

    if ($server_address) {
      $this->server_host = $server_address->nodeValue;
    }

    if ($server_ssh_user) {
      $this->server_ssh_user = $server_ssh_user->nodeValue;
    }

    if ($backup_path) {
      $this->backup_path = $backup_path->nodeValue . '/binlogs';
    }

    if ($temporary_path) {
      $this->temporary_path = $temporary_path->nodeValue;
    }
    else {
      $this->temporary_path = '/tmp';
    }

    if ($binlogs_directory) {
      $this->binlogs_directory = $binlogs_directory->nodeValue;
    }

    if ($binlogs_index_filename) {
      $this->binlogs_index_filename = $binlogs_index_filename->nodeValue;
    }

    if ($live_binlogs_retention_policy) {
      $this->live_binlogs_retention_policy = intval($live_binlogs_retention_policy->nodeValue);
    }

    if ($archive_binlogs_retention_policy) {
      $this->archive_binlogs_retention_policy = intval($archive_binlogs_retention_policy->nodeValue);
    }
  }

  /**
   * Process scripts arguments and options and put them into attributes
   *
   * @return void
   */
  protected function processArguments() {
    $this->mysql_user     = $this->input->getArgument('mysql_user');
    $this->mysql_password = $this->input->getArgument('mysql_password');
    $encryption_password  = $this->input->getOption('encryption_password');
    $encryption_algorithm = $this->input->getOption('encryption_algorithm');

    if ($encryption_password) {
      $this->use_encryption      = true;
      $this->encryption_password = $encryption_password;
    }
    else {
      $this->use_encryption = false;
    }

    if ($encryption_algorithm) {
      $this->encryption_algorithm = $encryption_algorithm;
    }
  }

  /**
   * Performs binlogs rotation
   *
   * @throws Exception
   *
   * @return void
   * @todo   Reduce cyclomatic complexity
   */
  protected function performBinlogsRotation() {
    $now = new DateTime();
    if (!$this->shell->fileExists($this->backup_path)) {
      $ret_code = $this->shell->mkdir($this->backup_path, true);
      if (!ShellCommand::checkErrors($ret_code)) {
        $error_msg = "Cannot create backup directory " . $this->backup_path . "\n" . $ret_code['error'];
        throw new Exception($error_msg);
      }
    }

    // Getting binlogs list
    $binlogs_list_path = $this->binlogs_directory;

    $ret_find = $this->shell->find($binlogs_list_path, false, false, true, true, '*bin.0*');

    if (!ShellCommand::checkErrors($ret_find)) {
      $error_msg = "Cannot list binlogs directory";
      throw new Exception($error_msg);
    }

    if (count($ret_find['output']) === 0) {
      $this->out(
        $this->output,
        "<info>No binlogs found, there is nothing to rotate</info>"
      );

      return;
    }

    $logs_list = $ret_find['output'];

    // Flushing logs after getting a list of it
    $this->flushLogs();

    foreach ($logs_list as $logfile) {
      $filename      = pathinfo($logfile['filename']);
      $creation_date = new DateTime($logfile['creation_date']);

      $creation_date_as_hours = intval($creation_date->getTimestamp() / 3600);
      $now_as_hours           = intval($now->getTimestamp() / 3600);

      $days_to_creation_date = intval($now_as_hours - $creation_date_as_hours);

      // Rotating live binlogs only if they are older than 'live_binlogs_retention_policy' hours
      if ($days_to_creation_date > $this->live_binlogs_retention_policy) {

        $expected_filename_archive = $filename['basename'] . '.tar.gz';
        $expected_filename         = $expected_filename_archive;

        $this->out(
          $this->output,
          '<comment>Rotating binlog ' . $filename['basename'] . '</comment>'
        );

        if ($this->use_encryption) {
          $expected_filename .= '.aes';
        }

        //Checking if the file already exists
        if ($this->shell->fileExists($this->backup_path . '/' . $expected_filename)) {
          $this->out(
            $this->output,
            '<comment>' . $expected_filename . ' already exists in ' . $this->backup_path . ', skipping it!</comment>'
          );
          continue;
        }

        //Archiving the file
        $ret_tar = $this->shell->tar(
          $this->temporary_path . '/' . $expected_filename_archive,
          $logfile['filename'],
          true,
          'gz',
          $filename['dirname']
        );

        if (!ShellCommand::checkErrors($ret_tar)) {
          $error_msg = 'An error occured during the compression of ' . $logfile['filename'] . "\nError message is:\n"
            . $ret_tar['error'] . "\n"
            . "Cannot continue binlogs rotation as it would create an inconsistency of the binlogs stream";

          //Removing avorted file
          $this->shell->rm($this->temporary_path . '/' . $expected_filename_archive);

          throw new Exception($error_msg);
        }

        if ($this->use_encryption) {
          // Ciphering the file
          $ret_cipher = $this->shell->openssl(
            $this->temporary_path . '/' . $expected_filename_archive,
            $this->temporary_path . '/' . $expected_filename,
            $this->encryption_password,
            $this->encryption_algorithm
          );

          if (!ShellCommand::checkErrors($ret_cipher)) {
            $error_msg = 'Cannot cipher ' . $expected_filename_archive . "\nError message is:\n"
              . $ret_cipher['error'] . "\n"
              . "Cannot continue binlogs rotation as it would create an inconsistency of the binlogs stream";

            $this->shell->rm($this->temporary_path . '/' . $expected_filename_archive);

            throw new Exception($error_msg);
          }
        }

        // Moving the archive to the backup dir
        $ret_copy = $this->shell->cp(
          $this->temporary_path . '/' . $expected_filename,
          $this->backup_path,
          false,
          false
        );

        if (!ShellCommand::checkErrors($ret_copy)) {
          $error_msg = 'Cannot copy ' . $expected_filename_archive . "\n"
            . $ret_copy['error'];

          throw new Exception($error_msg);
        }

        // Removing tmp file
        $this->shell->rm($this->temporary_path . '/' . $expected_filename_archive, false, false);

        // Removing the binlogs if it's older than 'live_binlogs_retention_policy' days
        $this->out(
          $this->output,
          '<info>' . $filename['basename'] . ' is ' . $days_to_creation_date . ' hours old, removing it</info>'
        );

        $binlogs_filepath = $filename['dirname'] . '/' . $filename['basename'];
        $this->shell->rm($binlogs_filepath, false, false);

        $this->out(
          $this->output,
          '<info>Rotation done for ' . $filename['basename'] . '</info>'
        );
      }
    }

    $this->out(
      $this->output,
      '<comment>Binlogs rotation done, performing archives rotation</comment>'
    );

    // Gettings archives list in order to rotate those older than archive_retention_policy
    $ret_find = null;
    $ret_find = $this->shell->find($this->backup_path, false, false, true, true);

    if (!ShellCommand::checkErrors($ret_find)) {
      $this->out(
        $this->output,
        "<comment>Cannot get a list of archived binlogs, skipping this part!\n" . $ret_find['error'] . '</comment>'
      );

      return;
    }

    $archived_files_list = $ret_find['output'];

    foreach ($archived_files_list as $archived_file) {
      $creation_date         = new DateTime($archived_file['creation_date']);

      $creation_date_as_days = intval($creation_date->getTimestamp() / 86400);
      $now_as_days           = intval($now->getTimestamp() / 86400);

      $days_to_creation_date = intval($now_as_days - $creation_date_as_days);

      if ($days_to_creation_date > $this->archive_binlogs_retention_policy) {
        $this->out(
          $this->output,
          '<comment>' . $archived_file['filename'] . ' is ' . $days_to_creation_date . ' days old, removing it</comment>'
        );

        $this->shell->rm($archived_file['filename'], false, false);
      }
    }

    $this->out(
      $this->output,
      '<info>Archive rotation done!</info>'
    );
  }

  /**
   * Flush binary logs on the servers
   *
   * @throws Exception
   *
   * @return void
   */
  protected function flushLogs() {
    $sql = "FLUSH LOGS";

    $this->out(
      $this->output,
      "<comment>Flushing binary logs</comment>"
    );

    $statement = $this->dbHandler->prepare($sql);

    if (!$statement->execute()) {
      $pdo_error = $statement->errorInfo();
      $error_msg = "Failed to flush binary logs!\n"
        . $pdo_error[2];

      throw new Exception($error_msg);
    }

    $this->out(
      $this->output,
      "<info>Successfully flushed binary logs</info>"
    );
  }
}
