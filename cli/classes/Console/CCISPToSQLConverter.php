<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\FileUtil\CCSVFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZipArchive;


/**
 * A class that convert the CISP base file (in CSV) to an SQL format
 */
class CCISPToSQLConverter extends Command {

  /** @var OutputInterface */
  protected $output;

  /** @var InputInterface */
  protected $input;

  /** @var string The path of the CISP CSV directory */
  protected $input_base_path;

  /** @var string The path of the archive containing the base file in SQL */
  protected $output_base_path;

  /** @var string The path of the SQL import file */
  protected $import_file;

  static $files_to_tables = array(
    "chapitre.csv" => array(
      "table"         => "chapitre",
      "fields"        => array(
        "lettre"        => "string",
        "description"   => "string",
        "note"          => "string"
      )
    ),
    "cisp.csv"       => array(
      "table"         => "cisp",
      "fields"        => array(
        "code_cisp"     => "string",
        "libelle"       => "string",
        "codes_cim10"   => "string",
        "inclusion"     => "string",
        "exclusion"     => "string",
        "description"   => "string",
        "consideration" => "string",
        "note"          => "string"
      )
    ),
    "procedure.csv" => array(
      "table"         => "procedure",
      "fields"        => array(
        "identifiant"   => "string",
        "description"   => "string"
      )
    )
  );

  /**
   * @inheritdoc
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $style = new OutputFormatterStyle('blue', null, array('bold'));
    $output->getFormatter()->setStyle('b', $style);

    $style = new OutputFormatterStyle(null, 'red', array('bold'));
    $output->getFormatter()->setStyle('error', $style);
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('ox-convert:cisp')
      ->setDescription('Convert the CISP csv file to MySQL dump')
      ->setHelp('CISP basefile to SQL converter')
      ->addOption(
        'input',
        'i',
        InputOption::VALUE_REQUIRED,
        'file path'
      )
      ->addOption(
        'output',
        'o',
        InputOption::VALUE_OPTIONAL,
        'The output archive path',
        __DIR__ . '/../../../modules/dPcim10/base/cisp.tar.gz'
      );
  }

  /**
   * @throws Exception
   *
   * @return void
   */
  protected function getParams() {
    $this->input_base_path = $this->input->getOption('input');
    $this->output_base_path = $this->input->getOption('output');

    if (!is_dir($this->input_base_path) || !is_readable($this->input_base_path)) {
      throw new Exception("Cannot read dir {$this->input_base_path}");
    }

    if ((!is_file($this->output_base_path) && !is_dir($this->output_base_path)) || !is_readable($this->output_base_path)) {
      $type = is_file($this->output_base_path) ? 'file' : 'dir';
      throw new Exception("Cannot read {$type} {$this->output_base_path}");
    }
  }

  /**
   * Output timed text
   *
   * @param string $text Text to print
   *
   * @return void
   */
  protected function out($text) {
    $this->output->writeln(CMbDT::strftime("[%Y-%m-%d %H:%M:%S]") . " - $text");
  }

    /**
     * @inheritdoc
     * @throws Exception
     */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->input  = $input;
    $this->output = $output;

    $this->getParams();

    if (is_file($this->output_base_path)) {
      $this->out('Extracting the Mediboard CISP database archive.');

      $path = __DIR__ . '/../../../tmp/cim10/cisp';

      set_error_handler(array($this, 'handleFileSystemError'));
      if (!CMbPath::extract($this->output_base_path, $path)) {
        throw new Exception('Unable to extract the CISP archive');
      }
      $this->out('Mediboard CISP database archive extracted');

      $this->output_base_path = $path;
    }


    $this->import_file = "{$this->output_base_path}/data.sql";

    if (file_exists($this->import_file)) {
      file_put_contents($this->import_file, '');
    }

    $files = CMbPath::getFiles($this->input_base_path);
    foreach ($files as $file_path) {
      $file_name = pathinfo($file_path, PATHINFO_BASENAME);
      $extension = pathinfo($file_path, PATHINFO_EXTENSION);

      if ($extension === 'csv') {
        $this->out("Converting data from file {$file_name}");
        $this->importFile($file_name, $file_path);
        $this->out("File {$file_name} converted");
      }
    }

    $this->out('Compressing the final archive');

    $this->createArchive();

    $this->out('Extraction complete');

    return self::SUCCESS;
  }

  /**
   * Make SQL queries from the data of the given file
   *
   * @param string $file The file's name
   * @param string $path The file's path
   *
   * @return void
   */
  protected function importFile($file, $path) {
    $table = self::$files_to_tables[$file]["table"];
    $fields = self::$files_to_tables[$file]["fields"];

    $fields_list = array();
    foreach ($fields as $field => $type) {
      $fields_list[] = "`{$field}`";
    }
    $fields_sql = implode(', ', $fields_list);

    $csv = new CCSVFile($path);
    $csv->setColumnNames(array_keys($fields));

    $csv->jumpLine(1);

    $query = '';
    $total = $csv->countLines();
    $line = 1;
    $n = 1;
    while ($data = $csv->readLine(true)) {
      if ($n === 1) {
        $query .= "INSERT INTO `$table` ({$fields_sql}) VALUES\n";
      }

      $values = array();
      foreach ($fields as $field => $type) {
        if (!array_key_exists($field, $data)) {
          continue;
        }

        if (in_array($data[$field], array('', '-'))) {
          $values[] = 'NULL';
        }
        elseif ($type == 'string') {
          $values[] = "'" . addslashes($data[$field]) . "'";
        }
        else {
          $values[] = $data[$field];
        }
      }

      $query .= '  (' . implode(', ', $values) . ')';
      $n++;
      $line++;

      if ($n < 1000 && $line < $total) {
        $query .= ",\n";
      }
      else {
        $query .= ";\n";
        $n = 1;
      }
    }

    file_put_contents($this->import_file, "{$query}\n", FILE_APPEND);
  }

  /**
   * Create the archive
   *
   * @return void
   */
  protected function createArchive() {
    $where_is = (stripos(PHP_OS, 'WIN') !== false) ? 'where' : 'which';
    exec("$where_is tar", $tar);
    $path = __DIR__ . '/../../../modules/dPcim10/base';
    if ($tar) {
      $cmd = "tar -czf {$path}/cisp.tar.gz -C {$this->output_base_path} ./tables.sql ./data.sql";
      exec($cmd, $result);
    }
    else {
      $zip = new ZipArchive();
      $zip->open("{$path}/cisp.zip", ZipArchive::OVERWRITE);
      $zip->addFile("{$this->output_base_path}/tables.sql", 'cisp/tables.sql');
      $zip->addFile($this->import_file, 'cisp/data.sql');
      $zip->close();
    }

    CMbPath::remove($this->output_base_path);
  }

  /**
   * An error handler that catch the error returned by the CMbPath functions and throws an exception
   *
   * @param integer $type    The PHP error type
   * @param string  $message The error message
   *
   * @return bool
   * @throws Exception
   */
  protected function handleFileSystemError($type, $message) {
    if ($type === E_USER_WARNING) {
      throw new Exception($message);
    }
    else {
      return false;
    }
  }
}
