<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;


use Exception;
use Ox\Cli\ExportDB;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Classe d'exportation d'une base de données MSSQL vers MySQL
 * export:MSSQL command
 */
class ExportMSSQL extends ExportDB
{

  private $restore;
  private $dump;

  /**
   * @inheritdoc
   */
  protected function configure()
  {
    $this
      ->setName('ox-export:mssql')
      ->setDescription('MSSQL database exporting tool')
      ->setHelp('MSSQL database dump to MySQL')
      ->addOption(
        'config',
        'c',
        InputOption::VALUE_OPTIONAL,
        'The config file which will be used',
        __DIR__ . "/../../export/mssql/config.ini"
      )
      ->addOption(
        'restore',
        null,
        InputOption::VALUE_OPTIONAL,
        'Restore a dump into a database'
      )
      ->addOption(
        'dump',
        null,
        InputOption::VALUE_OPTIONAL,
        'Create a dump file'
      );
  }

  /**
   * @inheritdoc
   */
  protected function getParams()
  {
    $this->ini_file_path = $this->input->getOption('config');
    $this->restore = $this->input->getOption('restore');
    $this->dump = $this->input->getOption('dump');

    if (!is_readable($this->ini_file_path)) {
      throw new Exception("Cannot read configuration file: {$this->ini_file_path}");
    }

    if (!$this->config = parse_ini_file($this->ini_file_path, true)) {
      throw new Exception("Cannot parse configuration file: {$this->ini_file_path}");
    }

    if ($this->restore) {
      $from_connection = $this->config['mssql_restore'];
      $host_mssql = escapeshellarg($from_connection['host']);

      $req = sprintf(
        "RESTORE DATABASE %s FROM DISK='%s' WITH REPLACE, MOVE '%s' TO '%s', MOVE '%s' TO '%s'",
        $from_connection['db_name'], $from_connection['bak_path'], $from_connection['data_logique'], $from_connection['path_data'],
        $from_connection['log_logique'], $from_connection['path_log']
      );
      $this->from_cmd = sprintf("sqlcmd -S %s -E -Q %s", $host_mssql, escapeshellarg($req));
    }
    $to_connection = $this->config['conversion'];
    $host_mssql = escapeshellarg($to_connection['host_mssql']);
    $username_mssql = escapeshellarg($to_connection['username_mssql']);
    $password_mssql = escapeshellarg($to_connection['password_mssql']);
    $db_source = escapeshellarg($to_connection['db_source']);

    if ($this->dump) {
      $this->to_cmd = sprintf(
        "m2sagent.exe --as_dbo --dump --dest=\"%s\" --engine=\"myISAM\" --mode=0 --mssqlh=%s --mssqlu=%s --mssqlp=%s --src=%s",
        $this->dump, $host_mssql, $username_mssql, $password_mssql, $db_source
      );
    }
    else {
      $db_target = escapeshellarg($to_connection['db_target']);
      $host_mysql = escapeshellarg($to_connection['host_mysql']);
      $username_mysql = escapeshellarg($to_connection['username_mysql']);
      $password_mysql = escapeshellarg($to_connection['password_mysql']);
      $port = escapeshellarg($to_connection['port']);

      $this->to_cmd = sprintf(
        "m2sagent.exe --as_dbo --dest=%s --engine=\"myISAM\" --mode=0 --mssqlh=%s --mssqlu=%s --mssqlp=%s --mysqlh=%s" .
        " --mysqlu=%s --mysqlp=%s --port=%s --src=%s",
        $db_target, $host_mssql, $username_mssql, $password_mssql, $host_mysql, $username_mysql, $password_mysql, $port, $db_source
      );
    }
  }

  /**
   * Test de connexion à une base de données MS SQL
   *
   * @param String $dbhost Adresse de connexion
   * @param String $dbname Nom de la base de données
   * @param String $dbuser Nom d'utilisateur
   * @param String $dbpass Mot de passe
   *
   * @return boolean
   */
  protected function testConnection($dbhost, $dbname, $dbuser, $dbpass)
  {
    $connectionInfo = array("Database" => $dbname, "UID" => $dbuser, "PWD" => $dbpass);
    $conn = sqlsrv_connect($dbhost, $connectionInfo);

    if ($conn) {
      $this->out("Connexion à la BDD MS SQL OK.");
      sqlsrv_close($conn);

      return true;
    }
    else {
      return false;
    }
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (!function_exists("sqlsrv_connect")) {
      throw new Exception("Sqlsrv extension not installed");
    }
    parent::execute($input, $output);
    if ($this->restore) {
      $time = time();
      $this->out('Database restore starting...');
      $this->out($this->from_cmd);
      $this->from("");

      $this->out('Database restored. Elapsed: ' . gmdate("H:i:s", (time() - $time)));
    }
    if (!$this->testConnection(
      $this->config["conversion"]["host_mssql"], $this->config["conversion"]["db_source"],
      $this->config["conversion"]["username_mssql"], $this->config["conversion"]["password_mssql"]
    )) {
      throw new Exception("Cannot connect to MS SQL server.");
    }
    $time = time();
    $this->out('Database export starting...');
    $this->out($this->to_cmd);
    $this->to("");
    $this->out('Export done! Elapsed: ' . gmdate("H:i:s", (time() - $time)));
    if ($this->dump) {
      $this->out(sprintf("Dump file : %s", $this->dump));
    }
    return self::SUCCESS;
  }
}
