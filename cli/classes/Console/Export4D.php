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
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * export:4d command
 */
class Export4D extends ExportDB {
  /**
   * @see parent::configure()
   */
  protected function configure() {
    $this
      ->setName('ox-export:4d')
      ->setDescription('4D database exporting tool')
      ->setHelp('4D database dump to MySQL')
      ->addOption(
        'db-version',
        null,
        InputOption::VALUE_REQUIRED,
        '4DServer version number'
      )
      ->addOption(
        'path',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Root path',
        realpath(__DIR__ . "/../../../")
      );
  }

  /**
   * @see parent::getParams()
   */
  protected function getParams() {
    $this->db_version = $this->input->getOption('db-version');
    $this->path       = $this->input->getOption('path');

    if (!is_dir($this->path)) {
      throw new InvalidArgumentException("'$this->path' is not a valid directory.");
    }

    $this->ini_file_path = "{$this->path}/cli/export/4d/v{$this->db_version}.ini";

    if (!is_readable($this->ini_file_path)) {
      throw new Exception("Cannot read configuration file: {$this->ini_file_path}");
    }

    if (!$this->config = parse_ini_file($this->ini_file_path, true)) {
      throw new Exception("Cannot parse configuration file: {$this->ini_file_path}");
    }

    $from_connection = $this->config['4d_connection'];
    $this->from_cmd  = "php {$this->path}/cli/export/4d/4d-mysqldump.php -h={$from_connection['host']} -u={$from_connection['username']} -p={$from_connection['password']}";

    $to_connection = $this->config['mysql_connection'];
    $this->to_cmd  = "mysql -h {$to_connection['host']} -u {$to_connection['username']} -p{$to_connection['password']} -D {$to_connection['database']}";
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    parent::execute($input, $output);

    $tables = $this->getTablesToExport();
    if (!$tables) {
      throw new Exception('No 4D tables to export');
    }

    $time = time();
    $this->out('Database export starting...');
    foreach ($tables as $_table) {
      $tmp = tempnam(sys_get_temp_dir(), "4D_{$_table}_");

      $_time = time();
      $this->out("Migrating {$this->tables[$_table]}...");

      $this->from(" -t={$this->tables[$_table]} > {$tmp}");
      $this->to(" < {$tmp}");

      $this->out('Migration done! Elapsed: ' . gmdate("H:i:s", (time() - $_time)));
      unlink($tmp);
    }

    $this->out('Export done! Elapsed: ' . gmdate("H:i:s", (time() - $time)));

    return self::SUCCESS;
  }

  /**
   * @return array|null
   */
  protected function getTables() {
    $tables = $this->from(' -l');

    if ($tables && is_array($tables)) {
      $all_tables = array();

      foreach ($tables as $_table) {
        $all_tables[mb_strtolower($_table, 'UTF-8')] = $_table;
      }

      return $this->tables = $all_tables;
    }

    return null;
  }

  /**
   * @return array
   * @throws Exception
   */
  protected function getTablesToExport() {
    if (!$this->getTables()) {
      throw new Exception('Cannot list 4D tables');
    }

    $tables_to_extract = explode(' ', $this->config['4d_tables']['tables']);
    $tables_to_extract = array_map(
      function ($v) {
        return mb_strtolower($v, 'UTF-8');
      }, $tables_to_extract
    );

    return $tables = array_intersect(array_keys($this->tables), $tables_to_extract);
  }
}
