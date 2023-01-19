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
use Ox\Core\CMbString;
use Ox\Core\FileUtil\CCSVFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get the countires from https://www.insee.fr/fr/information/3720946#titre-bloc-21
 */
class CCodesPaysInsee extends Command {
  /** @var OutputInterface */
  protected $output;

  /** @var InputInterface */
  protected $input;

  /** @var string $file_path */
  protected $file_path;

  /** @var CCSVFile */
  protected $csv;

  protected $line;

  protected $datas = [];

  protected $map = [
    'cog'      => 'code_insee',
    'libcog'   => 'nom_fr',
    'codeiso2' => 'alpha_2',
    'codeiso3' => 'alpha_3',
    'codenum3' => 'numerique',
  ];

  protected $file_output;

  protected $dump;

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
      ->setName('ox-geo:generate-country')
      ->setAliases(['ox-geo:gc'])
      ->setDescription('Generate a MySQL dump of countries table')
      ->setHelp('Generate a MySQL dump of countries table')
      ->addOption(
        'file-path',
        'p',
        InputOption::VALUE_REQUIRED,
        'Countries file path'
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

    $this->file_output = ($this->input->getOption('file-output')) ?: "tmp/countries.sql";
    $this->dump      = fopen($this->file_output, 'w+');

    $fp        = fopen($this->file_path, 'r');
    $this->csv = new CCSVFile($fp, CCSVFile::PROFILE_OPENOFFICE);

    $this->csv->setColumnNames($this->csv->readLine());
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

    while ($this->line = $this->bindLine()) {
      if (!is_array($this->line) || !$this->line['numerique']) {
        continue;
      }

      $this->datas[] = $this->line;
    }

    $this->csv->close();

    $this->writeDrop();
    $this->writeCreate();
    $this->writeDatas();

    fclose($this->dump);

    return self::SUCCESS;
  }

  /**
   * Map a line with $this->map
   *
   * @return array|string
   */
  protected function bindLine() {
    $line = $this->csv->readLine(true, true);

    if (!is_array($line)) {
      return $line;
    }

    if ($line['actual'] == 2) {
      return 'outdated';
    }

    $new_line = [];
    foreach ($line as $_key => $_value) {
      if (isset($this->map[$_key])) {
        $new_line[$this->map[$_key]] = str_replace("'", "\\'", utf8_decode($_value));
      }
    }

    $new_line['nom_fr'] = ucfirst(CMbString::lower($new_line['nom_fr']));

    return $new_line;
  }

  /**
   * Write the create statement
   *
   * @return void
   */
  protected function writeCreate() {
    $create = "CREATE TABLE `pays` (
                `numerique` mediumint(3) unsigned zerofill NOT NULL,
                `code_insee` char(5) NOT NULL,
                `alpha_3` char(3) NOT NULL DEFAULT '',
                `alpha_2` char(2) NOT NULL DEFAULT '',
                `nom_fr` varchar(255) NOT NULL DEFAULT '',
                PRIMARY KEY (`alpha_3`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1;\n\n";

    fwrite($this->dump, $create);
  }

  /**
   * Write the drop table statement
   *
   * @return void
   */
  protected function writeDrop() {
    $drop = "DROP TABLE IF EXISTS `pays`;\n\n";

    fwrite($this->dump, $drop);
  }

  /**
   * Write the datas into the dump file
   *
   * @return void
   */
  protected function writeDatas() {
    $insert = "INSERT INTO `pays` (" . implode(',', $this->map) . ") VALUES ";

    fwrite($this->dump, $insert);

    $max = count($this->datas);
    for ($i = 0; $i < $max; $i++) {
      $line = "('" . implode("','", $this->datas[$i]) . "')";

      $line .= ($i < ($max - 1)) ? ',' : ';';

      fwrite($this->dump, $line);
    }
  }
}
