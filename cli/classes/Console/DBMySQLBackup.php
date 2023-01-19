<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use DateTime;
use DOMElement;
use Exception;
use Ox\Cli\DBManagementCommand;
use Ox\Cli\MysqlCommand;
use Ox\Cli\NotificationException;
use Ox\Cli\ShellCommand;
use Ox\Core\CMbString;
use PDO;
use PDOException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class DBMySQLBackup
 *
 * @package Ox\Cli\Console
 */
class DBMySQLBackup extends DBManagementCommand {
  /** @var  array */
  protected $databases_to_backup;
  /** @var  bool */
  protected $interactive_mode = false;
  /** @var float */
  protected $compression_ratio = 0.4;
  /** @var int */
  protected $default_rotation_policy = 2;
  /** @var bool */
  protected $perform_hotcopy = false;
  /** @var bool */
  protected $rotate_only = false;


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('db:mysqlbackup')
      ->setDescription('Backups a MySQL database')
      ->setHelp('Perform a backup of a mysql database')
      ->addOption(
        'host',
        's',
        InputOption::VALUE_OPTIONAL,
        'Server address to perform the backup'
      )
      ->addOption(
        'user',
        'u',
        InputOption::VALUE_OPTIONAL,
        'MySQL user to perform the backup'
      )
      ->addOption(
        'password',
        'p',
        InputOption::VALUE_OPTIONAL,
        'MySQL login password'
      )
      ->addOption(
        'mysql_datadir',
        'm',
        InputOption::VALUE_OPTIONAL,
        'Data directory of MySQL'
      )
      ->addOption(
        'databases',
        'd',
        InputOption::VALUE_OPTIONAL,
        'List of the databases to backup (separated by a ,)'
      )
      ->addOption(
        'encryption_password',
        'c',
        InputOption::VALUE_OPTIONAL,
        'Password for the encryption of the archive'
      )
      ->addOption(
        'encryption_algorithm',
        'a',
        InputOption::VALUE_OPTIONAL,
        'Encryption algorithm to use (default: aes-256-cbc)'
      )
      ->addOption(
        'non_interactive_mode',
        'o',
        InputOption::VALUE_NONE,
        'Execute as non interactive'
      )
      ->addOption(
        'hotcopy',
        'y',
        InputOption::VALUE_OPTIONAL,
        'Whether we want to perform a temporary hotcopy or no (default value is false)'
      );
  }

  /**
   * @param InputInterface  $input  Input Interface
   * @param OutputInterface $output Output Interface
   *
   * @return int
   * @throws NotificationException|Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->input           = $input;
    $this->output          = $output;
    $this->question_helper = $this->getHelper('question');
    $this->shell           = new MysqlCommand();
    //$this->shell->verbose = true;

    $this->databases_to_backup = array();

    $this->loadConfig();
    // Process script arguments and options
    $this->processArguments();

    if (!$this->interactive_mode) {
      $this->executeNonInteractiveMode();

      return self::SUCCESS;
    }

    $this->showHeader();

    if (!$this->isRoot()) {
      $output->writeln(
        '<comment>'
        . "WARNING\n"
        . "This script should be executed as root as it requires high privileges.\n"
        . "Unless you have authorized the current user to manipulate mysql owned content, expect permission denied messages!\n\n\n"
        . '</comment>'
      );
    }

    if (!$this->mysql_user || !$this->server_host) {
      $this->askMySQLCredentials();
    }

    if (!$this->backup_path) {
      $this->askBackupPath();
    }

    if (!$this->temporary_path) {
      $this->askTemporaryPath();
    }

    $this->askUseEncryption();

    $dsn = 'mysql:dbname=mysql;host=' . $this->server_host;

    $this->setShellCommandHostname();

    try {
      $this->dbHandler = new PDO($dsn, $this->mysql_user, $this->mysql_password);
    }
    catch (Exception $e) {
      $this->out($this->output, "<error>Cannot connect to the database\n" . $e->getMessage() . "</error>");
      exit(1);
    }

    $this->out(
      $this->output,
      '<info>Successfully connected to the database!</info>'
    );

    $selected_databases = $this->askDatabasesToBackup();

    try {
      $this->performBackup($selected_databases);
    }
    catch (Exception $e) {
      throw new NotificationException($e->getMessage());
    }

    return self::SUCCESS;
  }

  /**
   * @inheritdoc
   */
  protected function loadConfig() {
    $xpath = $this->getXMLConfigurationFile();

    // Get server address tag, expected to get only one
    $server_address       = $xpath->query("//server_address");
    $server_ssh_user      = $xpath->query("//ssh_user");
    $backup_path          = $xpath->query("//backup_path");
    $temporary_path       = $xpath->query("//temporary_path");
    $rotation_policy      = $xpath->query("//mysqlbackup/default_retention_policy");
    $databases            = $xpath->query("//mysqlbackup/databases_to_backup/database");
    $encryption_password  = $xpath->query("/db_management/encryption_password");
    $encryption_algorithm = $xpath->query("/db_management/encryption_algorithm");


    if ($server_address->length > 0) {
      $this->server_host = $server_address->item(0)->nodeValue;
    }

    if ($server_ssh_user->length > 0) {
      $this->server_ssh_user = $server_ssh_user->item(0)->nodeValue;
    }

    if ($backup_path->length > 0) {
      $this->backup_path = $backup_path->item(0)->nodeValue;
    }

    if ($temporary_path->length > 0) {
      $this->temporary_path = $temporary_path->item(0)->nodeValue;
    }
    else {
      $this->temporary_path = $this->backup_path;
    }

    if ($encryption_password->length > 0) {
      $this->encryption_password = $encryption_password->item(0)->nodeValue;
    }
    else {
      $this->encryption_password = null;
    }

    if ($encryption_password->length > 0) {
      $this->encryption_algorithm = $encryption_algorithm->item(0)->nodeValue;
    }

    if ($rotation_policy->length > 0) {
      $this->default_rotation_policy = intval($rotation_policy->item(0)->nodeValue);
    }

    $databases_to_backup = array();
    foreach ($databases as $database) {
      $databases_to_backup[] = $database->nodeValue;
    }

    $this->databases_to_backup = $databases_to_backup;
  }

  /**
   * Display script informations
   *
   * @return void
   */
  protected function showHeader() {
    $this->output->writeln(
      <<<EOT
      <fg=yellow;bg=black>
 ____
| __ )  __ _  ___| | ___   _ _ __  
|  _ \ / _` |/ __| |/ / | | | '_ \ 
| |_) | (_| | (__|   <| |_| | |_) |
|____/ \__,_|\___|_|\_\\\__,_| .__/ 
                            |_|    
</fg=yellow;bg=black>
EOT
    );
  }

  /**
   * Process scripts arguments and options and put them into attributes
   *
   * @return void
   */
  protected function processArguments() {
    $non_interactive_mode = $this->input->getOption('non_interactive_mode');
    /*$this->backup_path    = $this->input->getOption('backup_path');
    $this->temporary_path = $this->input->getOption('tmp_path');
    $mysql_datadir        = $this->input->getOption('mysql_datadir');*/
    $this->mysql_user     = $this->input->getOption('user');
    $this->mysql_password = $this->input->getOption('password');
    /*$encryption_password  = $this->input->getOption('encryption_password');
    $encryption_algorithm = $this->input->getOption('encryption_algorithm');*/
    $databases_str   = $this->input->getOption('databases');
    $server_host     = $this->input->getOption('host');
    $perform_hotcopy = $this->input->getOption('hotcopy');

    if (!$non_interactive_mode) {
      $this->interactive_mode = true;
    }
    else {
      $this->interactive_mode = false;
    }

    /*if ($encryption_password) {
      $this->use_encryption      = true;
      $this->encryption_password = $encryption_password;
    }
    else {
      $this->use_encryption = false;
    }

    if ($encryption_algorithm) {
      $this->encryption_algorithm = $encryption_algorithm;
    }*/

    if ($server_host) {
      $this->server_host;
    }

    if ($perform_hotcopy) {
      $this->perform_hotcopy = true;
    }

    // If database_to_backup have not been set previously ($this->loadConfig())
    if (!$this->databases_to_backup) {
      if ($databases_str) {
        $this->databases_to_backup = explode(',', $databases_str);
      }
      else {
        $this->databases_to_backup = null;
      }
    }
  }

  /**
   * Execute the script in non interactive mode
   *
   * @return void
   * @throws Exception
   */
  protected function executeNonInteractiveMode() {
    if (!$this->backup_path) {
      throw new NotificationException(
        'Cannot perform backup, backup path is not specified!'
      );
    }

    if (!$this->temporary_path) {
      $this->temporary_path = $this->backup_path;
    }

    if (!$this->mysql_user) {
      throw new NotificationException(
        'Cannot perform backup, mysql user is not specified!'
      );
    }

    if (!$this->databases_to_backup || count($this->databases_to_backup) === 0) {
      throw new NotificationException(
        'Cannot perform backup, no databases specified!'
      );
    }

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

    $this->setShellCommandHostname();

    $selected_databases = $this->getDatabasesToBackup($this->databases_to_backup);

    try {
      $this->performBackup($selected_databases);
    }
    catch (Exception $e) {
      throw new NotificationException(
        $e->getMessage()
      );
    }
  }

  /**
   * Performs the backup
   *
   * @param array $selected_databases List of the databases to backup
   *
   * @throws Exception
   * @return void
   */
  protected function performBackup($selected_databases) {

    // Removing failed dumps
    $not_removed_dumps = $this->removeDumps($selected_databases);

    if ($not_removed_dumps > 0) {
      $this->out(
        $this->output,
        "<comment>"
        . "[WARNING]: " . $not_removed_dumps . " dumps have not been deleted.\n"
        . "This can lead to several errors during the hotcopy (eg: permission denied)"
        . "</comment>"
      );

      if ($this->interactive_mode) {

        $question = new ConfirmationQuestion('Do you want to continue [y/N] ?', false);

        $validate = $this->question_helper->ask(
          $this->input,
          $this->output,
          $question
        );

        if (!$validate) {
          return;
        }
      }
    }

    /*if (!$this->checkDiskSpaceAvailability($selected_databases)) {
      throw new Exception('Cannot perform backup because disk space is missing');
    }*/

    foreach ($selected_databases as $database) {
      $db_name = $database['TABLE_SCHEMA'];

      $this->createDatabaseBackupPath($db_name);
    }


    // Performing backup for each databases
    foreach ($selected_databases as $database) {
      $backup_database_path = $this->backup_path . '/' . $database['TABLE_SCHEMA'] . '-db';
      //$now                 = date('Y-m-dTH-i-s');
      $now         = new DateTime();
      $date_format = 'Y-m-d\TH-i-s';

      $this->flushQueryCache();

      $this->out(
        $this->output,
        '<info>Checking disk space availability for database ' . $database['TABLE_SCHEMA'] . '</info>'
      );

      if (!$this->checkDiskSpaceAvailability($database)) {
        throw new Exception('Cannot perform backup because disk space is missing');
      }

      // Mysqlhotcopy
      $this->out(
        $this->output,
        '<comment>Dumping database into ' . $this->temporary_path . '/' . $database['TABLE_SCHEMA'] . '</comment>'
      );

      $master_status = $this->shell->showMasterStatus();

      if ($master_status) {
        //Formatting master status
        $master_status_formated = 'Binlogs Position for the backup of ' . $database['TABLE_SCHEMA'] . "\n";
        $master_status_formated .= 'Backup date: ' . $now->format($date_format) . "\n";
        $master_status_formated .= "======================================================================\n";
        $master_status_formated .= 'File: ' . $master_status['File'] . "\n";
        $master_status_formated .= 'Position: ' . $master_status['Position'] . "\n";
        $master_status_formated .= 'Binlog_Do_DB: ' . $master_status['Binlog_Do_DB'] . "\n";
        $master_status_formated .= 'Binlog_Ignore_DB: ' . $master_status['Binlog_Ignore_DB'] . "\n";

        $this->shell->writeFile($backup_database_path . '/' . $database['TABLE_SCHEMA'] . '.index', $master_status_formated);
      }


      $return_array = $this->shell->mysqlhotcopy($database['TABLE_SCHEMA'], $this->temporary_path);

      if ($return_array['return_code'] !== 0) {
        $error_msg = $return_array["error"];

        if (!preg_match("|failed \(256\) while copying files|i", $error_msg)) {
          throw new Exception(
            '<error>An error occurred during the hotcopy of the database ' . $database['TABLE_SCHEMA'] . "\n"
            . $return_array['error']
            . '</error>'
          );
        }
        else {
          $this->out(
            $this->output,
            '<comment>Dump done with copy error (permission issue during hotcopy)</comment>'
          );
        }
      }
      else {
        $this->out(
          $this->output,
          '<info>Dump done!</info>'
        );
      }

      $this->out(
        $this->output,
        '<comment>Archiving \'' . $database['TABLE_SCHEMA'] . '\'...</comment>'
      );

      // Compressing the archive once the hotcopy is done
      $dump_path           = $this->temporary_path . '/' . $database['TABLE_SCHEMA'];
      $backup_archive_file = $backup_database_path . '/'
        . $database['TABLE_SCHEMA']
        . '-'
        . $now->format($date_format)
        . '.tar.gz';

      $tar_return = $this->shell->tar(
        $backup_archive_file,
        $dump_path,
        true,
        'gz',
        $this->temporary_path
      );

      if ($tar_return['return_code'] !== 0) {
        throw new Exception(
          '<error>An error occurred during archiving of the database ' . $database['TABLE_SCHEMA'] . "\n"
          . $tar_return['error']
          . '</error>'
        );
      }

      $this->out(
        $this->output,
        '<info>Archiving done!</info>'
      );

      $this->out(
        $this->output,
        '<comment>Removing dump of \'' . $database['TABLE_SCHEMA'] . '\' database'
      );

      if (!$this->removeDump($database['TABLE_SCHEMA'])) {
        $this->out(
          $this->output,
          '<comment>Cannot remove dump of ' . $database['TABLE_SCHEMA'] . '</comment>'
        );
      }
      else {
        $this->out(
          $this->output,
          '<info>Dump successfully removed!</info>'
        );
      }

      // Cipher backup
      if ($this->backupWantsEncryption($database['TABLE_SCHEMA'])) {
        $this->out(
          $this->output,
          '<comment>Ciphering backup...</comment>'
        );

        $encrypted_file = $backup_archive_file . '.aes';

        $openssl_return = $this->shell->openssl(
          $backup_archive_file,
          $encrypted_file,
          $this->encryption_password,
          $this->encryption_algorithm
        );

        if ($openssl_return['return_code'] !== 0) {
          throw new Exception(
            '<error>'
            . 'Cannot cipher archive'
            . $openssl_return['error']
            . '</error>'
          );
        }

        $this->out(
          $this->output,
          '<info>Ciphering done!</info>'
        );

        $this->shell->rm($backup_archive_file, false, false);
      }

      // Rotation des backups
      $ok = $this->backupsRotation($database, $backup_database_path, $now);

      if (!$ok) {
        continue;
      }
    }

    $this->out(
      $this->output,
      '<info>Successful backup!</info>'
    );
  }

  /**
   * Checks if the backup needs encryption or not
   *
   * @param string $database_name Database name
   *
   * @return bool|string
   * @throws Exception
   */
  private function backupWantsEncryption($database_name) {
    /*
    * If we are in interactive mode, we bypass everything by getting the use_encryption value,
    * otherwise, we use the per database attribute value
    */
    if ($this->interactive_mode) {
      return $this->use_encryption;
    }

    $xpath    = $this->getXMLConfigurationFile();
    $database = $xpath->query("/db_management/mysqlbackup/databases_to_backup/database[text()='$database_name']");

    if ($database->length === 0) {
      return false;
    }

    $database             = $database->item(0);
    $encryption_attribute = $database->getAttribute("use_encryption");

    if (!$encryption_attribute) {
      return false;
    }

    // Cannot use encryption if not password has been set
    if (!$this->encryption_password) {
      return false;
    }

    if ($encryption_attribute === "1") {
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Ask mysql credentials
   *
   * @return void
   */
  protected function askMySQLCredentials() {
    $default_username = 'root';

    $this->out(
      $this->output,
      '<question>Mysql login credentials are unknown, please set them</question>'
    );

    $host = $this->question_helper->ask(
      $this->input,
      $this->output,
      new Question(
        'Server Host [default: ' . $this->server_host . '] : ',
        $this->server_host
      )
    );

    $username = $this->question_helper->ask(
      $this->input,
      $this->output,
      new Question(
        'MySQL username [default: ' . $default_username . '] : ',
        $default_username
      )
    );

    $password_question = new Question('MySQL Password : ');
    $password_question->setHidden(true);
    $password_question->setHiddenFallback(false);

    $password = $this->question_helper->ask(
      $this->input,
      $this->output,
      $password_question
    );

    $this->server_host    = $host;
    $this->mysql_user     = $username;
    $this->mysql_password = $password;

    $this->shell->mysql_user     = $username;
    $this->shell->mysql_password = $password;
    $this->shell->mysql_host     = $host;
  }

  /**
   * Ask the path of the backup
   *
   * @return void
   */
  protected function askBackupPath() {
    $default_backup_path = '/var/backup';
    $this->backup_path   = '';

    $this->backup_path = $this->promptPathAskingWidget('Backup Directory', $default_backup_path);
  }

  /**
   * Asks temporary path for the backup
   *
   * @return void
   */
  protected function askTemporaryPath() {
    $default_temporary_path = $this->backup_path;
    $this->temporary_path   = '';

    $this->temporary_path = $this->promptPathAskingWidget('Temporary Directory (for the dump)', $default_temporary_path);
  }

  /**
   * Asks and return a list of databases to backup
   *
   * @return array List of string of the databases to backup
   * @throws Exception
   */
  protected function askDatabasesToBackup() {
    $separator = '|';
    $databases_list = $this->getDatabasesSize();
    $total_db_size  = array_sum(array_column($databases_list, 'DB_SIZE'));

    $databases = array('[ALL]'.$separator.CMbString::toDecaBinary($total_db_size));

    foreach ($databases_list as $database) {
      $db_label    = $database['TABLE_SCHEMA'] .$separator.CMbString::toDecaBinary($database['DB_SIZE']);
      $databases[] = $db_label;
    }

    $question = new ChoiceQuestion(
      'Select the database(s) to backup',
      $databases,
      0
    );
    $question->setErrorMessage('Value "%s" is invalid');
    $question->setMultiselect(true);

    $selected = $this->question_helper->ask(
      $this->input,
      $this->output,
      $question
    );

    $selected_db_names = array();
    foreach ($selected as $db) {
      $selected_db_names[] = explode($separator, $db)[0];
    }

    if (in_array("[ALL]", $selected_db_names, true)) {
      $selected_databases  = $databases_list;
    }
    else {
      $selected_databases = array();
      foreach ($databases_list as $_db) {
        if (in_array($_db['TABLE_SCHEMA'], $selected_db_names)) {
          $selected_databases[] = $_db;
        }
      }
    }

    return $selected_databases;
  }

  /**
   * Get a list of the databases to backup from a list of databases name
   *
   * @param array $databases_list Names of the databases
   *
   * @return array
   * @throws Exception
   */
  protected function getDatabasesToBackup($databases_list) {
    $databases          = $this->getDatabasesSize();
    $selected_databases = array();

    foreach ($databases as $database) {
      if (in_array($database['TABLE_SCHEMA'], $databases_list)) {
        $selected_databases[] = $database;
      }
    }

    return $selected_databases;
  }

  /**
   * Get the size of the databases
   *
   * @return array List of the databases
   * @throws Exception
   */
  protected function getDatabasesSize() {
    $sql_query = "SELECT TABLE_SCHEMA, ROUND(SUM(DATA_LENGTH + INDEX_LENGTH), 2) AS \"DB_SIZE\"
                  FROM information_schema.TABLES
                  GROUP BY TABLE_SCHEMA ORDER BY DB_SIZE DESC";

    try {
      $statement = $this->dbHandler->prepare($sql_query);
    }
    catch (PDOException $e) {
      throw new Exception($e->getMessage());
    }

    $ok = $statement->execute();

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
   * Gets a list of tables with their size (Data + Index Size)
   *
   * @param string $db_name Db to perform the listing
   *
   * @return array List of tables with their size
   * @throws Exception
   */
  protected function getTablesInfos($db_name) {
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
                  FROM information_schema.TABLES
                  WHERE TABLE_SCHEMA=:db_name";

    try {
      $statement = $this->dbHandler->prepare($sql_query);
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
   * Checks if there is enought disk space to perform the backup
   *
   * @param array $selected_database Selected database
   *
   * @return bool
   */
  protected function checkDiskSpaceAvailability($selected_database) {

    $total_dump_size       = $selected_database['DB_SIZE'];
    $estimated_backup_size = $total_dump_size * $this->compression_ratio;

    if ($this->use_encryption) {
      $estimated_backup_size = $estimated_backup_size * 2;
    }

    //Getting partitions info of the server
    $backup_partition_info    = $this->shell->df($this->backup_path);
    $temporary_partition_info = $this->shell->df($this->temporary_path);

    if ($this->backup_path !== $this->temporary_path) {
      // Vérification de l'espace disque pour la partition temporaire
      if ($total_dump_size >= $temporary_partition_info['output'][0]['free_space']) {
        $disk_size_report = CMbString::toDecaBinary($total_dump_size) . " required disk space\n"
          . CMbString::toDecaBinary($temporary_partition_info['output'][0]['free_space'])
          . ' free space on the partition';

        $this->out(
          $this->output,
          '<error>Cannot perform backup, disk space on partition '
          . $temporary_partition_info['output'][0]['mount_point']
          . ' will not be able to sustain the dump!'
          . "\n" . $disk_size_report . "\n"
          . '</error>'
        );

        return false;
      }

      if ($estimated_backup_size >= $backup_partition_info['output'][0]['free_space']) {
        $disk_size_report = CMbString::toDecaBinary($total_dump_size) . " required disk space\n"
          . CMbString::toDecaBinary($temporary_partition_info['output'][0]['free_space'])
          . ' free space on the partition';

        $this->out(
          $this->output,
          '<error>Cannot perform backup, disk space on partition '
          . $backup_partition_info['output'][0]['mount_point']
          . ' will not be able to sustain the archive!'
          . "\n" . $disk_size_report . "\n"
          . '</error>'
        );

        return false;
      }
    }
    else {
      if ($total_dump_size + $estimated_backup_size >= $temporary_partition_info['output'][0]['free_space']) {
        $disk_size_report = CMbString::toDecaBinary($total_dump_size + $estimated_backup_size) . " required disk space\n"
          . CMbString::toDecaBinary($temporary_partition_info['output'][0]['free_space'])
          . ' free space on the partition';

        $this->out(
          $this->output,
          '<error>Cannot perform backup, disk space on partition '
          . $backup_partition_info['output'][0]['mount_point']
          . ' will not be able to sustain the backup!'
          . "\n" . $disk_size_report . "\n"
          . '</error>'
        );

        return false;
      }
    }

    return true;
  }

  /**
   * Checks if there is enought disk space to perform the backup
   *
   * @param string $selected_databases
   * UNUSED
   *
   * @return bool
   */
  /*protected function checkDiskSpaceAvailability($selected_databases) {

    $total_dump_size       = array_sum(array_column($selected_databases, 'DB_SIZE'));
    $estimated_backup_size = $total_dump_size * $this->compression_ratio;

    if ($this->use_encryption) {
      $estimated_backup_size = $estimated_backup_size * 2;
    }

    //Getting partitions info of the server
    $backup_partition_info    = $this->shell->df($this->backup_path);
    $temporary_partition_info = $this->shell->df($this->temporary_path);

    if ($this->backup_path !== $this->temporary_path) {
      // Vérification de l'espace disque pour la partition temporaire
      if ($total_dump_size >= $temporary_partition_info['output'][0]['free_space']) {
        $disk_size_report = CMbString::toDecaBinary($total_dump_size) . " required disk space\n"
          . CMbString::toDecaBinary($temporary_partition_info['output'][0]['free_space'])
          . ' free space on the partition';

        $this->out(
          $this->output,
          '<error>Cannot perform backup, disk space on partition '
          . $temporary_partition_info['output'][0]['mount_point']
          . ' will not be able to sustain the dump!'
          . "\n" . $disk_size_report . "\n"
          . '</error>'
        );

        return false;
      }

      if ($estimated_backup_size >= $backup_partition_info['output'][0]['free_space']) {
        $disk_size_report = CMbString::toDecaBinary($total_dump_size) . " required disk space\n"
          . CMbString::toDecaBinary($temporary_partition_info['output'][0]['free_space'])
          . ' free space on the partition';

        $this->out(
          $this->output,
          '<error>Cannot perform backup, disk space on partition '
          . $backup_partition_info['output'][0]['mount_point']
          . ' will not be able to sustain the archive!'
          . "\n" . $disk_size_report . "\n"
          . '</error>'
        );

        return false;
      }
    }
    else {
      if ($total_dump_size + $estimated_backup_size >= $temporary_partition_info['output'][0]['free_space']) {
        $disk_size_report = CMbString::toDecaBinary($total_dump_size + $estimated_backup_size) . " required disk space\n"
          . CMbString::toDecaBinary($temporary_partition_info['output'][0]['free_space'])
          . ' free space on the partition';

        $this->out(
          $this->output,
          '<error>Cannot perform backup, disk space on partition '
          . $backup_partition_info['output'][0]['mount_point']
          . ' will not be able to sustain the backup!'
          . "\n" . $disk_size_report . "\n"
          . '</error>'
        );

        return false;
      }
    }

    return true;
  }*/

  /**
   * Flushes the query cache
   *
   * @throws Exception
   * @return void
   */
  protected function flushQueryCache() {
    $sql_query = "FLUSH QUERY CACHE";

    $this->out(
      $this->output,
      '<comment>Flushing query cache...</comment>'
    );

    $statement = $this->dbHandler->prepare($sql_query);

    if (!$statement->execute()) {
      $pdo_error_message = $statement->errorInfo();
      $error_msg         = "Failed to flush query cache!\n"
        . $pdo_error_message[2];

      throw new Exception($error_msg);
    }

    $this->out(
      $this->output,
      '<info>Query cache successfully flushed!</info>'
    );
  }

  /**
   * Removes dumps of a previous backup that would have crashed for a reason or another
   *
   * @param array $selected_databases List of databases used to remove the failed dumps
   *
   * @return int number of dumps that have not been removed
   */
  protected function removeDumps($selected_databases) {
    $not_removed_dumps = 0;

    // Removing previous dumps that would have not been removed due to error during the backup
    foreach ($selected_databases as $database) {
      if (!$this->removeDump($database['TABLE_SCHEMA'])) {
        $not_removed_dumps++;
      }
    }

    return $not_removed_dumps;
  }

  /**
   * Remove a mysqlhotcopy dump
   *
   * @param string $database_name Name of the dump to remove
   *
   * @return bool
   */
  protected function removeDump($database_name) {
    $db_path = $this->temporary_path . '/' . $database_name;

    if ($this->shell->fileExists($db_path)) {
      $return_info = $this->shell->rm($db_path, true, false);

      if ($return_info['return_code'] !== 0) {
        $error_message = "An error occured during failed dump removal\n"
          . $return_info['error'];

        $this->out(
          $this->output,
          '<comment>' . $error_message . '</comment>'
        );

        return false;
      }
    }

    return true;
  }


  /**
   * Create the directories to perform
   *
   * @param string $database_name Name of the database
   *
   * @throws Exception
   *
   * @return void
   */
  protected function createDatabaseBackupPath($database_name) {
    $database_path = $this->backup_path . '/' . $database_name . '-db';

    $ret = $this->shell->mkdir($database_path, true);

    if ($ret['return_code'] !== 0) {
      throw new Exception("Cannot create path for the backup\n" . $ret['error']);
    }
  }

  /**
   * Ask the user if he wants to use the ssl encryption to cipher the backup
   *
   * @return void
   */
  protected function askUseEncryption() {


    $validate = $this->question_helper->ask(
      $this->input,
      $this->output,
      new ConfirmationQuestion(
        'Do you want to cipher the backup [y/N] ?',
        false
      )
    );

    if ($validate) {
      $this->use_encryption  = true;
      $encryption_algorithms = array(
        "aes-128-cbc",
        "aes-256-cbc"
      );

      $selected = $this->question_helper->ask(
        $this->input,
        $this->output,
        new ChoiceQuestion(
          'Select the encryption algorithm',
          $encryption_algorithms,
          1
        )
      );

      $this->encryption_algorithm = $selected;



      do {
        do {

          $password_question = new Question('Encryption password ?');
          $password_question->setHidden(true);

          $password = $this->question_helper->ask(
            $this->input,
            $this->output,
            $password_question
          );

          if (!$password) {
            $this->out(
              $this->output,
              '<comment>You cannot set an empty password</comment>'
            );
          }
        } while (!$password);

        $password_confirm_question = new Question('Confirm the password');
        $password_confirm_question->setHidden(true);

        $confirm_password = $this->question_helper->ask(
          $this->input,
          $this->output,
          $password_confirm_question
        );

        if ($password !== $confirm_password) {
          $this->out(
            $this->output,
            '<comment>Passwords does not match</comment>'
          );
        }
      } while ($password !== $confirm_password);

      $this->encryption_password = $password;
    }
  }

  /**
   * Gets the retention policy for a given database name
   *
   * @param string $database_name Database name
   *
   * @return bool|int returns false if the database doesn't exists in the XML file (this should not happens), if the attribute
   *                  'retention_policy' doesn't exists or is not numeric, returns the default policy value and the real value
   *                  otherwise
   * @throws Exception
   */
  public function getRetentionPolicy($database_name) {
    $xpath    = $this->getXMLConfigurationFile();
    $database = $xpath->query("//mysqlbackup/databases_to_backup/database[text()='" . $database_name . "']");

    if ($database->length === 0) {
      return false;
    }

    /** @var DOMElement $node */
    $node = $database[0];

    $retention_attribute = $node->getAttribute("retention_policy");

    // If the attribute does not exists we need to return the default retention policy
    if (!$retention_attribute) {
      return $this->default_rotation_policy;
    }

    if (!is_numeric($retention_attribute)) {
      return $this->default_rotation_policy;
    }

    return $retention_attribute;
  }

  /**
   * Performs backups rotation
   *
   * @param array    $database             array object describing the database
   * @param string   $backup_database_path string path to the database backup path
   * @param DateTime $now                  DateTime date time when the backup started
   *
   * @return bool
   * @throws Exception
   */
  protected function backupsRotation($database, $backup_database_path, $now) {
    // Finding all backup files and display both path and creation date
    $find_return = $this->shell->find(
      $backup_database_path,
      false,
      false,
      true,
      true,
      $database["TABLE_SCHEMA"] . '-*'
    );

    if ($find_return['return_code'] !== 0) {
      $this->out(
        $this->output,
        '<comment>[WARNING]: Cannot get backups list for ' . $database['TABLE_SCHEMA'] . " database</comment>\n"
        . $find_return['error']
      );

      return false;
    }

    $backup_list = $find_return['output'];


    $this->out(
      $this->output,
      '<comment>Rotating old backups</comment>'
    );

    foreach ($backup_list as $backup_file) {
      $retention_policy = intval($this->getRetentionPolicy($database["TABLE_SCHEMA"]));

      if (!$retention_policy) {
        continue;
      }

      $fileinfo          = pathinfo($backup_file['filename']);
      $exploded_filename = explode('.', $fileinfo["filename"]);
      $filename          = $exploded_filename[0];

      $database_needle = $database["TABLE_SCHEMA"] . '-';
      $backup_date     = substr($filename, strlen($database_needle));
      $backup_date     = str_replace('T', ' ', $backup_date);

      $creation_date = DateTime::createFromFormat("Y-m-d H-i-s", $backup_date);

      //Converting timestamp to day
      $creation_date_as_days = intval($creation_date->getTimestamp() / 86400);
      $now_as_days           = intval($now->getTimestamp() / 86400);

      // strtotime?
      $subbed_date = intval($now_as_days - $creation_date_as_days);

      if ($subbed_date >= $retention_policy) {
        $this->out(
          $this->output,
          $backup_file['filename'] . ' is ' . $retention_policy . ' days old or more, removing it'
        );

        $return_rm = $this->shell->rm($backup_file['filename'], false, false);

        if (!ShellCommand::checkErrors($return_rm)) {
          $this->out(
            $this->output,
            '<error>Cannot remove ' . $backup_file['filename'] . "\n"
            . $return_rm['error']
            . '</error>'
          );
        }
      }
    }

    $this->out(
      $this->output,
      '<info>Backup rotation done</info>'
    );

    return true;
  }

}
