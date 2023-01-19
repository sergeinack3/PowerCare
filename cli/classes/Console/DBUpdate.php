<?php

/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Exception;
use Ox\Cli\CommandLinePDO;
use Ox\Cli\MediboardCommand;
use Ox\Core\CAppUI;
use Ox\Core\CMbConfig;
use Ox\Core\Database\SetupUpdater;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class DBUpdate
 *
 * @package Ox\Cli\Console
 */
class DBUpdate extends MediboardCommand implements IAppDependantCommand
{
    use LockableTrait;

    /**
     * @var int The value must match the id of the user defined in the mediboard.sql file
     */
    public const USER_PHPUNIT_ID = 14;

    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var SymfonyStyle */
    protected $io;

    /** @var string */
    protected $path;

    /** @var bool */
    protected $report;

    /** @var bool */
    protected $dryrun;

    /** @var array */
    protected $params = [];

    /** @var CMbConfig */
    protected $config;

    /** @var string */
    protected $db_host;

    /** @var string */
    protected $db_name;

    /** @var string */
    protected $db_user;

    /** @var string */
    protected $db_pass;

    /** @var CommandLinePDO */
    protected $pdo;

    /** @var SetupUpdater */
    private $updater;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-db:update')
            ->setDescription('Upgrades database schema')
            ->addOption('config', 'c', InputOption::VALUE_NONE, 'Use parameters from configuration file')
            ->addOption('install', 'i', InputOption::VALUE_NONE, 'Install every available module')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulates the update without execution')
            ->addOption('report', 'r', InputOption::VALUE_NONE, 'Reports database status')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Working copy root', dirname(__DIR__, 3) . '/')
            ->addOption('db_host', null, InputOption::VALUE_REQUIRED, 'The db host name', 'db')
            ->addOption('db_user', null, InputOption::VALUE_REQUIRED, 'The db username', 'dev')
            ->addOption('db_pass', null, InputOption::VALUE_REQUIRED, 'The db password', 'oxdev17!')
            ->addOption('db_name', null, InputOption::VALUE_REQUIRED, 'The db name', 'mediboard');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output    = $output;
        $this->input     = $input;
        $this->path      = $input->getOption('path');
        $this->report    = $input->getOption('report');
        $this->dryrun    = $input->getOption('dry-run');
        $this->io        = new SymfonyStyle($this->input, $this->output);

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("'$this->path' is not a valid directory.");
        }

        if (!$this->lock($this->path)) {
            $this->io->caution(
                [
                    $this->getName() . ' command is already running on directory ' . $this->path,
                    'This command will consequently be skipped.',
                ]
            );
            return self::INVALID;
        }

        if ($this->input->getOption('config')) {
            $this->db_host = CAppUI::conf('db std dbhost');
            $this->db_name = CAppUI::conf('db std dbname');
            $this->db_user = CAppUI::conf('db std dbuser');
            $this->db_pass = CAppUI::conf('db std dbpass');
        } else {
            $this->db_host = $this->input->getOption('db_host');
            $this->db_name = $this->input->getOption('db_name');
            $this->db_user = $this->input->getOption('db_user');
            $this->db_pass = $this->input->getOption('db_pass');
        }

        $this->params = [
            'host'           => $this->db_host,
            'database'       => $this->db_name,
            'user'           => $this->db_user,
            'password'       => $this->db_pass,
            'admin_password' => $this->db_pass,
        ];

        $this->checkConnexion()->checkDatabase(true);

        $this->updater = (new SetupUpdater())
            ->enableCommandLineMode($this->io)
            ->setInstallMode($this->input->getOption('install'))
            ->setDryRunMode($this->input->getOption('dry-run'))
            ->runAll();

        return $this->checkUpdateStatus();
    }

    /**
     * Compares each module revision against its setup latest revision
     *
     * @return void
     */
    private function checkUpdateStatus(): bool
    {
        $failures = $this->updater->getStatus();
        $modules  = $this->updater->getModules();

        if (!empty($failures)) {
            uasort(
                $failures,
                function ($a, $b) {
                    return strcmp($a["name"], $b["name"]);
                }
            );

            if ($this->report || $this->dryrun) {
                $this->io->createTable()
                    ->setHeaderTitle('Outdated modules (' . count($failures) . ')')
                    ->setHeaders(['Name', 'Current', 'Expected'])
                    ->setRows($failures)
                    ->render();
            }

            $reportMessage = [count($failures) . ' in ' . count($modules) . ' modules are outdated !'];

            if ($this->dryrun) {
                $reportMessage[] = "Run the following command to update them: 'bin/console " . $this->getName() . "'";
                $this->io->warning($reportMessage);
                return self::SUCCESS;
            }

            $this->io->error($reportMessage);
            return self::FAILURE;
        }

        $this->io->success(
            'All modules (' . count($modules) . ') are up to date.'
        );

        return self::SUCCESS;
    }

    /**
     * Create pdo & check connexion
     * @return self
     * @throws Exception
     */
    private function checkConnexion(): self
    {
        try {
            $this->pdo = new CommandLinePDO($this->params['host'], $this->params['user'], $this->params['password']);
        } catch (Exception $e) {
            if ($this->io->getVerbosity() === OutputInterface::VERBOSITY_VERBOSE) {
                throw $e;
            }
            throw new LogicException("Unable to connect to mysql:host={$this->params['host']}.");
        }

        return $this;
    }

    /**
     * @param bool $mustExist
     * @return self
     * @throws LogicException
     */
    private function checkDatabase(bool $mustExist = false): self
    {
        if ($mustExist) {
            if (!$this->pdo->isDatabaseExists($this->params['database'])) {
                throw new LogicException("Database {$this->params['database']} does not exist.");
            }

            $this->pdo = new CommandLinePDO(
                $this->params['host'],
                $this->params['user'],
                $this->params['password'],
                $this->params['database']
            );
        } else {
            if (!preg_match('/^[A-Z0-9_-]+$/i', $this->params['database'])) {
                throw new LogicException('Invalid database name (A-Z0-9_).');
            }
            if ($this->pdo->isDatabaseExists($this->params['database'])) {
                throw new LogicException("Database {$this->params['database']} already exists.");
            }
        }

        return $this;
    }
}
