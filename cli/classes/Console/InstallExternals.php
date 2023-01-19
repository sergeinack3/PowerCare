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
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\Import\CExternalDataSourceImport;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class InstallExternals
 *
 * @package Ox\Cli\Console
 */
class InstallExternals extends MediboardCommand
{
    /** @var OutputInterface */
    protected $output;

    /** @var InputInterface */
    protected $input;

    /** @var SymfonyStyle */
    protected $io;

    /** @var Stopwatch */
    private $stopwatch;

    /** @var string */
    protected $path;

    /** @var array */
    protected $params = [];

    /** @var array */
    protected $names = [];

    /** @var bool */
    protected $delete;

    /** @var CommandLinePDO */
    protected $pdo;

    /** @var CExternalDataSourceImport[] */
    protected $imports;

    /** @var array */
    protected $created = [];

    /** @var array */
    protected $durations;

    /**
     * @see parent::configure()
     */
    protected function configure(): void
    {
        $this
            ->setName('ox-install:externals')
            ->setDescription('Install external databases with data import')
            ->addArgument(
                'names',
                InputArgument::IS_ARRAY,
                'Which database do you want to create (separate multiple names with a space) ?'
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Working copy root',
                dirname(__DIR__, 3) . '/'
            )->addOption(
                'import',
                'i',
                InputOption::VALUE_NONE,
                'Import data',
            )->addOption(
                'delete',
                'd',
                InputOption::VALUE_NONE,
                'Delete existing databases',
            );
    }

    /**
     * @see parent::showHeader()
     */
    protected function showHeader(): void
    {
        $this->io->title("OX externals installation");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws Exception|InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output    = $output;
        $this->input     = $input;
        $this->io        = new SymfonyStyle($this->input, $this->output);
        $this->stopwatch = new Stopwatch(true);
        $this->names     = $this->input->getArgument('names');
        $this->delete    = $this->input->getOption('delete');
        $this->path      = $this->input->getOption('path');

        $this->showHeader();

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("'$this->path' is not a valid directory.");
        }

        // includes configs (legacy)
        require $this->path . '/includes/config_all.php';

        Cache::init($this->path);
        CAppUI::init();

        $this->loadImports();

        if ($this->delete) {
            $this->dropDatabases();
            $this->io->success('External databases deleted.');
        }

        $this->createDatabases();

        $this->importAllData();
        if ($this->io->isVerbose()) {
            $this->displayImportDurations();
        }
        $this->io->success(count($this->created) . ' External databases data imported.');

        return self::SUCCESS;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function loadImports(): void
    {
        $imports = CClassMap::getInstance()->getClassChildren(CExternalDataSourceImport::class);

        /** @var string $importClass */
        foreach ($imports as $importClass) {
            /** @var CExternalDataSourceImport $import */
            $this->imports[] = new $importClass();
        }
    }

    /**
     * Create pdo & check connexion
     *
     * @param CExternalDataSourceImport $import
     *
     * @return self
     * @throws Exception
     */
    private function checkConnexion(CExternalDataSourceImport $import): self
    {
        $host     = $this->getDatabaseConfig($import, 'dbhost');
        $user     = $this->getDatabaseConfig($import, 'dbuser');
        $password = $this->getDatabaseConfig($import, 'dbpass');

        if ($this->io->isDebug()) {
            $this->io->writeln("Trying to connect to host: {$host}...");
        }

        try {
            $this->pdo = new CommandLinePDO($host, $user, $password);
        } catch (Exception $e) {
            if ($this->io->isVerbose()) {
                throw $e;
            }
            throw new LogicException("Unable to connect to host: {$host} !");
        }

        return $this;
    }

    /**
     * @return void
     * @throws Exception|LogicException
     */
    private function dropDatabases(): void
    {
        foreach ($this->imports as $import) {
            $this->checkConnexion($import);
            $name = $import->getSourceNameForSQL();
            if ($this->pdo->isDatabaseExists($name)) {
                if (!$this->pdo->dropDatabase($name)) {
                    throw new LogicException("Unable to drop external database {$name}.");
                } elseif ($this->io->isDebug()) {
                    $this->io->info("External database {$name} dropped.");
                }
            }
        }
    }

    /**
     * @return void
     * @throws Exception|LogicException
     */
    private function createDatabases(): void
    {
        foreach ($this->imports as $import) {
            $name = $this->getDatabaseConfig($import, 'dbname');
            if (!empty($this->names) && !in_array($name, $this->names)) {
                continue;
            }

            $this->checkConnexion($import);

            if (!$this->pdo->isDatabaseExists($name)) {
                if (!$this->pdo->createDatabase($name)) {
                    throw new LogicException("Unable to create external database {$name}.");
                } else {
                    $this->created[] = $name;
                    if ($this->io->isDebug()) {
                        $this->io->info("External database {$name} created.");
                    }
                }
            } elseif ($this->io->isVerbose()) {
                $this->io->info("External database {$name} already exists.");
            }
        }
    }

    /**
     * @return self
     * @throws Exception
     */
    private function importAllData(): self
    {
        $this->io->progressStart(count($this->created));

        foreach ($this->imports as $import) {
            $databaseName = $this->getDatabaseConfig($import, 'dbname');
            if (!in_array($databaseName, $this->created)) {
                continue;
            }

            $name = get_class($import);
            $this->stopwatch->start($name);
            $importResult = $import->importDatabase();

            if ($this->io->isVerbose()) {
                $this->io->createTable()->setHeaderTitle('Messages for ' . $name)
                    ->setRows($import->getMessages())
                    ->render();
                if ($importResult) {
                    $this->io->success($name);
                } else {
                    $this->io->error($name);
                }
            }

            $this->durations[$name] = $this->stopwatch->stop($name)->getDuration();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        return $this;
    }

    private function displayImportDurations(): void
    {
        if (empty($this->durations)) {
            return;
        }

        $durations = $this->durations;
        arsort($durations);

        $table = $this->io->createTable()->setHeaderTitle('Durations')->setHeaders(['name', 'duration (ms)']);

        foreach ($durations as $name => $duration) {
            $table->addRow([$name, $duration]);
        }
        $table->render();
    }

    /**
     * @throws Exception
     */
    private function getDatabaseConfig(CExternalDataSourceImport $import, string $name): string
    {
        return CAppUI::conf('db ' . $import->getSourceName() . ' ' . $name);
    }
}
