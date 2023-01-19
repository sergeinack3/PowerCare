<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use Exception;
use Ox\Core\CMbDT;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportDB
 *
 * @package Ox\Cli
 */
abstract class ExportDB extends Command
{
    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var string Root path */
    protected $path;

    /** @var string Database version number */
    protected $db_version;

    /** @var string INI configuration file path */
    protected $ini_file_path;

    /** @var array INI configuration content */
    protected $config;

    /** @var string Main CMD for database migration */
    protected $from_cmd;

    /** @var string Destination CMD for database migration (mysql, etc.) */
    protected $to_cmd;

    /** @var array Table list */
    protected $tables;

    /**
     * @param InputInterface  $input  Input interface
     * @param OutputInterface $output Output interface
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('blue', null, ['bold']);
        $output->getFormatter()->setStyle('b', $style);

        $style = new OutputFormatterStyle(null, 'red', ['bold']);
        $output->getFormatter()->setStyle('error', $style);
    }

    /**
     * Output timed text
     *
     * @param string $text Text to print
     *
     * @return void
     */
    protected function out($text)
    {
        $this->output->writeln(CMbDT::strftime("[%Y-%m-%d %H:%M:%S]") . " - $text");
    }

    /**
     * @throws Exception
     * @see parent::configure()
     */
    protected function configure()
    {
        throw new Exception(__METHOD__ . " must be redefined");
    }

    /**
     * Display header information
     *
     * @return void
     */
    protected function showHeader()
    {
        $this->out('<fg=red;bg=black>' . $this->getDescription() . '</fg=red;bg=black>');
    }

    /**
     * Gets and sets arguments and options
     *
     * @return mixed
     * @throws Exception
     */
    abstract protected function getParams();

    /**
     * @param InputInterface  $input  Input interface
     * @param OutputInterface $output Output interface
     *
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = $output;

        $this->getParams();
        $this->showHeader();

        return self::SUCCESS;
    }

    /**
     * @param string $cmd Command
     *
     * @return mixed
     */
    protected function from($cmd)
    {
        exec("{$this->from_cmd} $cmd", $output);

        return $output;
    }

    /**
     * @param string $cmd Command
     *
     * @return mixed
     */
    protected function to($cmd)
    {
        exec("{$this->to_cmd} $cmd", $output);

        return $output;
    }
}
