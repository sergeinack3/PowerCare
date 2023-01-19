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
use Ox\Core\FileUtil\CFormattedFileReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get the countries postal codes from http://download.geonames.org/export/zip/
 */
class CZipCodeImport extends Command {
  /** @var OutputInterface */
  protected $output;

  /** @var InputInterface */
  protected $input;

  /** @var string $file_path */
  protected $file_path;

  /** @var string $country */
  protected $country;

  /** @var CFormattedFileReader */
  protected $file_reader;

  protected $line;

  protected $datas = [];

  protected $file_output;

  protected $dump;

  public static $countries = [
    "de" => "communes_allemagne",
    "es" => "communes_espagne",
    "gb" => "communes_gb",
    "pt" => "communes_portugal",
    "be" => "communes_belgique",
  ];

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
      ->setName('ox-geo:generate-cp')
      ->setAliases(['ox-geo:cp'])
      ->setDescription('Generate a MySQL dump of a country zipcode')
      ->setHelp('Generate a MySQL dump of a country zipcode')
      ->addOption(
        'file-path',
        'p',
        InputOption::VALUE_REQUIRED,
        'Country\'s zipcode file path'
      )
      ->addOption(
        'country',
        'c',
        InputOption::VALUE_REQUIRED,
        'Country alpha2 code ISO'
      )
      ->addOption(
        'file-output',
        'o',
        InputOption::VALUE_OPTIONAL,
        'Output file'
      );
  }

  /**
   * @return void
   * @throws Exception
   */
  protected function getParams() {
    $this->file_path = $this->input->getOption('file-path');

    if (!is_file($this->file_path) || !is_readable($this->file_path)) {
      throw new Exception("Cannot read file {$this->file_path}");
    }

    $this->country = $this->input->getOption('country');


    $this->file_output = ($this->input->getOption('file-output')) ?: "tmp/zipcode_{$this->country}.sql";
    $this->dump        = fopen($this->file_output, 'w+');

    $this->file_reader = new CFormattedFileReader($this->file_path, 0, false);
    $this->file_reader->setSeparator("\t");
    $this->file_reader->setSanitize(
      [
        "utf8_decode",
        "trim",
        "addslashes"
      ]
    );
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
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->input  = $input;
    $this->output = $output;

    $this->getParams();

    while ($this->line = $this->file_reader->readAndSanitizeLine(false)) {
      $this->datas[] = [$this->line[2], $this->line[1]];
    };

    $this->file_reader->close();

    $this->writeDrop();
    $this->writeCreate();
    $this->writeDatas();

    fclose($this->dump);

    return self::SUCCESS;
  }

  /**
   * Write the create statement
   *
   * @return void
   */
  protected function writeCreate() {
    $create = "CREATE TABLE `" . static::$countries[$this->country] . "` (
                `ville_id` INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
                `commune` varchar(180) NOT NULL DEFAULT '',
                `code_postal` varchar(8) NOT NULL,
                INDEX commune (commune),
                INDEX code_postal (code_postal)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1;\n\n";

    $this->writeToFile($create);
  }

  /**
   * Write the drop table statement
   *
   * @return void
   */
  protected function writeDrop() {
    $drop = "DROP TABLE IF EXISTS `" . static::$countries[$this->country] . "`;\n\n";

    $this->writeToFile($drop);
  }

  /**
   * Write the datas into the dump file
   *
   * @return void
   */
  protected function writeDatas() {
    $insert = "INSERT INTO `" . static::$countries[$this->country] . "` (commune, code_postal) VALUES ";

    $this->writeToFile($insert);

    $max = count($this->datas);
    for ($i = 0; $i < $max; $i++) {
      $line = "('" . implode("','", $this->datas[$i]) . "')";

      $line .= ($i < ($max - 1)) ? ',' : ';';

      $this->writeToFile($line);
    }
  }

  /**
   * @param string $content
   *
   * @return void
   */
  protected function writeToFile($content) {
    fwrite($this->dump, $content);
  }
}
